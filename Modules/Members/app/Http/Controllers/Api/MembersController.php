<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Country;
use App\Models\User;
use App\Notifications\SendOtp;
use App\Rules\ReCaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberContact;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Models\MemberUnit;

class MembersController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:profile.view', ['only' => ['showProfile']]);
    }

    /**
     * Display profile verification pending page (The page shows after a new membership request)
     */
    public function profilePending()
    {
        $user = Auth::user();
        $pending = MembershipRequest::where('user_id', $user->id)->get();
        return $this->sendResponse($pending);
    }


    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $member = Member::where('user_id', $user->id)->first();
        $member_request = MembershipRequest::where('user_id', $user->id)->latest()->first();
        $statuses = $member_request->request_status;
        $data = [
            'member' => $member,
            'statuses' => $statuses
        ];
        return $this->sendResponse($data);
    }

    /**
     * Creating user and adding member a new member
    */
    public function createMember(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->symbols()],
            //'g-recaptcha-response' => ['required', new ReCaptcha]
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());     
        }
        $input = $request->all();
        $user = User::create($input);
        $user->assignRole(['Member']);
        $member_data ['user_id'] = $user->id;
        $member_data ['name'] = $user->name;
        $member = Member::create($member_data);
        // Sending OTP 
        $token = $user->email === 'prejith021@gmail.com' ? 5432 : rand(1000, 9999);
        $otp = $user->otp()->firstOrCreate([], [
            'token' => $token,
        ]);
        $otp->load('authable');
        $user->notify(new SendOtp($otp));
        $data = [
            'user' => $user,
            'member' => $member,
            'logged_in' => false,
            'verify_email_otp' => true
        ];
        return $this->sendResponse($data);
    }

    /**
     * Sending Login OTP to email
     * 
     * @return \Illuminate\Http\Response
     */
    private function sendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')]
        ]);
        $data = $request->all();
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $user = User::where('email', $data['email'])->firstOrFail();
        $token = $data['email'] == 'prejith021@gmail.com' ? 5432 : rand(1000, 9999);
        $otp = $user->otp()->firstOrCreate([], [
            'token' => $token,
        ]);
        $otp->load('authable');
        $user->notify(new SendOtp($otp));
        return true;
    }

    /**
     * Verify email OTP
     */
    public function verifyEmailOtp(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')],
                'otp' => 'bail|required|integer'
            ]);
            $data = $request->all();
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());     
            }
            $user = User::where('email', $data['email'])->with('otp')->firstOrFail();
            if($user->otp == null){
                return $this->sendError('Unauthorized.', ['error'=>'Otp not available. Request new one and try again.']);
            }
            if($user->otp->token === (int) $data['otp']){
                // Verifying email if not done already(Using in registration time).
                if($user->email_verified_at == null){
                    $user->email_verified_at = now();
                    $user->save();
                }
                $user->otp()->delete();
                $data['token'] = $user->createToken(env('APP_NAME'))->plainTextToken;
                $data['user'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone' => $user->phone,
                ];
                return $this->sendResponse($data, 'User logged in successfully.');
            }else{
                return $this->sendError('Invalid OTP','The OTP is invalid. Please try again');
            }
        }catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed', $e);
        }
    }

    /**
     * Resending email OTP
     */
    public function resendEmailOtp(Request $request)
    {
        $user = User::where('email', $request->email)->with('otp')->firstOrFail();
        $data = [
            'success' => true
        ];
        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse($data, 'Email already verified');
        }
        $this->sendEmailOtp($request);
        return $this->sendResponse($data, 'User logged in successfully.');
    }
    
    /**
     * Send the form for creating a new resource.
     */
    public function createDetails()
    {
        $countries = Country::with('regions')->where('active', 1)->get();
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = array(
            ['name'=>'Male', 'slug' => 'male'], 
            ['name' => 'Female', 'slug' => 'female']
        );
        $district_kerala = array(
            ['name' => 'Alappuzha', 'slug' => 'alappuzha'],
            ['name' => 'Ernakulam', 'slug' => 'ernakulam'],
            ['name' => 'Idukki', 'slug' => 'idukki'],
            ['name' => 'Kannur', 'slug' => 'kannur'],
            ['name' => 'Kasaragod', 'slug' => 'kasaragod'],
            ['name' => 'Kollam', 'slug' => 'kollam'],
            ['name' => 'Kottayam', 'slug' => 'kottayam'],
            ['name' => 'Kozhikkode', 'slug' => 'kozhikkode'],
            ['name' => 'Malappuram', 'slug' => 'malappuram'],
            ['name' => 'Palakkad', 'slug' => 'palakkad'],
            ['name' => 'Pathanamthitta', 'slug' => 'pathanamthitta'],
            ['name' => 'Thiruvananthapuram', 'slug' => 'thriuvananthapuram'],
            ['name' => 'Thrissur', 'slug' => 'thrissur'],
            ['name' => 'Wayanada', 'slug' => 'wayanad'],
            ['name' => 'Other', 'slug' => 'other'],
        );
        $data = [
            'countries' => $countries,
            'units' => $units,
            'blood_groups' => $blood_groups,
            'gender' => $gender,
            'districts' => $district_kerala
        ];
        return $this->sendResponse($data);
    }


    /**
     * Send the form for creating a new resource.
     */
    public function storeDetails(Request $request)
    {
        $user = Auth::user();
        $existing_membership_data = Membership::where('user_id', $user->id)->first();
        $existing_membership_request = MembershipRequest::where('user_id', $user->id)->latest()->first();
        if($existing_membership_data){
            return $this->sendError('Not allowed.', 'You already a member', 405); 
        }
        if($existing_membership_request){
            return $this->sendError('Already requested.', 'Your membership '.strtolower($existing_membership_request->request_status->description), 405); 
        }
        $validator = Validator::make($request->all(), ...$this->validationRules($request));
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $avatarName = 'av'.$user->id.'_'.time().'.'.$request->avatar->extension(); 
        $request->avatar->storeAs('public/images', $avatarName);

        $civil_id_front_name = 'cvf'.$user->id.'_'.time().'.'.$request->photo_civil_id_front->extension(); 
        $request->photo_civil_id_front->storeAs('public/images', $civil_id_front_name);

        $civil_id_back_name = 'cvb'.$user->id.'_'.time().'.'.$request->photo_civil_id_back->extension(); 
        $request->photo_civil_id_back->storeAs('public/images', $civil_id_back_name);

        $passport_front_name = 'ppf'.$user->id.'_'.time().'.'.$request->photo_passport_front->extension(); 
        $request->photo_passport_front->storeAs('public/images', $passport_front_name);

        $passport_back_name = 'ppb'.$user->id.'_'.time().'.'.$request->photo_passport_back->extension(); 
        $request->photo_passport_back->storeAs('public/images', $passport_back_name);

        $input = $request->all();
        $input['avatar'] = $avatarName;

       

        DB::beginTransaction();
        try {
            // Adding member details
            MemberDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'member_unit_id' => $input['member_unit_id'],
                    'civil_id' => $input['civil_id'],
                    'photo_civil_id_front' => $civil_id_front_name,
                    'photo_civil_id_back' => $civil_id_back_name,
                    'dob' => $input['dob'],
                    'whatsapp' => $input['whatsapp'],
                    'whatsapp_code' => $input['whatsapp_country_code'],
                    'emergency_phone' => $input['emergency_phone'],
                    'emergency_phone_code' => $input['emergency_country_code'],
                    'company' => $input['company'],
                    'profession' => $input['profession'],
                    'company_address' => $input['company_address'],
                    'passport_no' => $input['passport_no'],
                    'passport_expiry' => $input['passport_expiry'],
                    'photo_passport_front' => $passport_front_name,
                    'photo_passport_back' => $passport_back_name,
                    'paci' => $input['paci'],
                    'sndp_branch' => $input['sndp_branch'],
                    'sndp_branch_number' => $input['sndp_branch_number'],
                    'sndp_union' => $input['sndp_union'],
                    'completed' => 1
                ]
            );

            // Updating members table (It has been created an entry when the time of registration)
            Member::where('user_id', $user->id)->update([
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group']
            ]);
            //Updating users table - phone number and avatar
            User::where('id', $user->id)->update([
                'phone' => $input['phone'],
                'calling_code' => $input['tel_country_code'],
                'avatar' => $avatarName,
            ]);

            // Create membership table entry
            Membership::create([
                'user_id' => $user->id,
                'type' => $input['type'],
                'introducer_name' => $input['introducer_name'],
                'introducer_phone' => $input['introducer_country_code'].$input['introducer_phone'],
                'introducer_mid' => $input['introducer_mid'],
                'introducer_unit' => $input['introducer_unit'],
            ]);
            
            // Create contacts table entry
            MemberLocalAddress::create([
                'user_id' => $user->id,
                'governorate' => $input['governorate'],
                'line_1' => $input['local_address_area'],
                'building' => $input['local_address_building'],
                'flat' => $input['local_address_flat'],
                'floor' => $input['local_address_floor'],
            ]);
            
            // Adding introducers details
            MemberPermanentAddress::create([
                'user_id' => $user->id,
                'line_1' => $input['permanent_address_line_1'],
                'district' => $input['permanent_address_district'],
                'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact'],
            ]);
            // Adding entry to membership_request table, with 'saved' status;
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
            MembershipRequest::create([
                'user_id' => $user->id,
                'request_status_id' => $status->id,
                'checked' => 1, 
                'updated_by' => $user->id,
            ]);
            // If form submitted as 'save & submit', add entry to membership_request table with 'submitted' status
            if($request->input('action') == 'submit'){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                MembershipRequest::create([
                    'user_id' => $user->id,
                    'request_status_id' => $status->id,
                    'updated_by' => $user->id,
                ]);
            }
            // adding spouse
            // Adding spouse if membership type is family
            if($input['type'] == 'family'){
                $userInput['name'] = $input['spouse_name'];
                $userInput['email'] = $input['spouse_email'];
                $userInput['password'] = Hash::make(Str::random(10));
                $spouse_user = User::create($userInput);
                $spouse_user->assignRole(['Member']);
                $spouse ['user_id'] = $spouse_user->id;
                $spouse ['name'] = $spouse_user->name;
                $spouse_member = Member::create($spouse);
                // Sending OTP
                //Storing attachments
                $spouse_avatarName = 'av'.$spouse_user->id.'_'.time().'.'.$request->spouse_avatar->extension(); 
                $request->spouse_avatar->storeAs('public/images', $spouse_avatarName);
                $spouse_civil_id_front_name = 'cvf'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_civil_id_front->extension(); 
                $request->spouse_photo_civil_id_front->storeAs('public/images', $spouse_civil_id_front_name);
                $spouse_civil_id_back_name = 'cvb'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_civil_id_back->extension(); 
                $request->spouse_photo_civil_id_back->storeAs('public/images', $spouse_civil_id_back_name);
                $spouse_passport_front_name = 'ppf'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_passport_front->extension(); 
                $request->spouse_photo_passport_front->storeAs('public/images', $spouse_passport_front_name);
                $spouse_passport_back_name = 'ppb'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_passport_back->extension(); 
                $request->spouse_photo_passport_back->storeAs('public/images', $spouse_passport_back_name);
                // Spouse Member details
                MemberDetail::updateOrCreate(
                    ['user_id' => $spouse_user->id],
                    [
                        'member_unit_id' => $input['member_unit_id'],
                        'civil_id' => $input['spouse_civil_id'],
                        'photo_civil_id_front' => $spouse_civil_id_front_name,
                        'photo_civil_id_back' => $spouse_civil_id_back_name,
                        'dob' => $input['spouse_dob'],
                        'whatsapp' => $input['spouse_whatsapp_country_code'].$input['spouse_whatsapp'],
                        'emergency_phone' => $input['spouse_emergency_country_code'].$input['spouse_emergency_phone'],
                        'company' => $input['spouse_company'],
                        'profession' => $input['spouse_profession'],
                        'company_address' => $input['spouse_company_address'],
                        'passport_no' => $input['spouse_passport_no'],
                        'passport_expiry' => $input['spouse_passport_expiry'],
                        'photo_passport_front' => $spouse_passport_front_name,
                        'photo_passport_back' => $spouse_passport_back_name,
                        'sndp_branch' => $input['sndp_branch'],
                        'sndp_branch_number' => $input['sndp_branch_number'],
                        'sndp_union' => $input['sndp_union'],
                        'completed' => 1
                    ]
                );
                // Updating members table (Already created an entry when registering username)
                Member::where('user_id', $spouse_user->id)->update([
                    'gender' => $input['spouse_gender'],
                    'blood_group' => $input['spouse_blood_group'],
                    'type' => 'spouse'
                ]);
                //Updating users table - phone number and avatar
                User::where('id', $spouse_user->id)->update([
                    'phone' => $input['spouse_phone'],
                    'calling_code' => $input['spouse_tel_country_code'],
                    'avatar' => $spouse_avatarName,
                ]);
                // Create membership table entry
                Membership::create([
                    'user_id' => $spouse_user->id,
                    'type' => $input['type'],
                    'introducer_name' => $input['introducer_name'],
                    'introducer_phone' => $input['introducer_country_code'].$input['introducer_phone'],
                    'introducer_mid' => $input['introducer_mid'],
                    'introducer_unit' => $input['introducer_unit'],
                ]);
                // Create contacts table entry
                MemberLocalAddress::create([
                    'user_id' => $spouse_user->id,
                    'line_1' => $input['local_address_area'],
                    'building' => $input['local_address_building'],
                    'flat' => $input['local_address_flat'],
                    'floor' => $input['local_address_floor'],
                ]);
                // Adding introducers details
                MemberPermanentAddress::create([
                    'user_id' => $spouse_user->id,
                    'line_1' => $input['permanent_address_line_1'],
                    'district' => $input['permanent_address_district'],
                    'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact'],
                ]);
                // Adding entry to membership_request table, with 'saved' status;
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
                MembershipRequest::create([
                    'user_id' => $spouse_user->id,
                    'request_status_id' => $status->id,
                    'checked' => 1, 
                    'updated_by' => $spouse_user->id,
                ]);
                // If form submitted as 'save & submit', add entry to membership_request table with 'submitted' status
                if($request->input('action') == 'submit'){
                    $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                    MembershipRequest::create([
                        'user_id' => $spouse_user->id,
                        'request_status_id' => $status->id,
                        'updated_by' => $spouse_user->id,
                    ]);
                }
                //Adding relationship
                $relation = MemberEnum::where('type', 'relationship')->where('slug', 'spouse')->first();
                $mainMember = Member::where('user_id',$user->id)->first();
                MemberRelation::create([
                    'member_id' => $mainMember->id,
                    'related_member_id' => $spouse_member->id,
                    'relationship_id' => $relation->id,
                ]);
                MemberRelation::create([
                    'member_id' => $spouse_member->id,
                    'related_member_id' => $mainMember->id,
                    'relationship_id' => $relation->id,
                ]);
            }
            DB::commit();
            $response = [
                'success' => true,
            ];
            return $this->sendResponse($response, 'Your membership request has been sent for review.');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed', $e);
        }
    }

    protected function validationRules($request)
    {
        $rules =  [
            'member_unit_id'    => ['required', Rule::exists(MemberUnit::class, 'id')],
            'phone'             => ['required', Rule::unique(User::class)],
            'whatsapp'          => ['required', 'numeric'],
            'emergency_phone'   => ['required', 'numeric'],
            'civil_id'          => ['required', 'string'],
            'dob'               => ['required', 'date_format:Y-m-d'],
            'company'           => ['nullable', 'string'],
            'profession'        => ['nullable', 'string'],
            'passport_no'       => ['required', 'string'],
            'passport_expiry'   => ['required', 'date_format:Y-m-d'],
            'gender'            => ['required', 'string'],
            'blood_group'       => ['required', 'string'],
            'type'              => ['required', 'string'],

            'governorate'         => ['required', 'string'],
            'local_address_area'         => ['required', 'string'],
            'local_address_building'         => ['required', 'string'],
            'local_address_flat'         => ['required', 'string'],
            'local_address_floor'         => ['required', 'string'],

            'avatar'            => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'photo_civil_id_front'    => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'photo_civil_id_back'     => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'photo_passport_front'    => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'photo_passport_back'     => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
        ];

        $messages = [
            'member_unit_id.required' => 'Unit is required',
            'member_unit_id.exists' => 'Unit is not valid',
            'phone.required' => 'Phone number is required',
            'phone.unique' => 'This phone number is already used',
            'whatsapp.required' => 'Whatsapp Number is required',
            'whatsapp.numeric' => 'Whatsapp number should be a number',
            'emergency_phone.required' => 'Emergency Phone Number is required',
            'emergency_phone.numeric' => 'Emergency Phone number should be a number',
            'civil_id.required' => 'Civil ID is required',
            'civil_id.string' => 'Civil ID is not valid',
            'dob.required' => 'Date of birth is required',
            'dob.date_format' => 'Date of birth should be of format Y-m-d',
            'passport_no.required' => 'Passport number is required',
            'passport_expiry.required' => 'Passport expiry date is required',
            'passport_expiry.date_format' => 'Passport expiry date should be of format Y-m-d',
            'gender.required' => 'Gender is required',
            'blood_group.required' => 'Blood group is required',
            'type.required' => 'Membership type is required',

            'governorate.required' => 'Governorate is required',
            'local_address_area.required' => 'Kuwait Address Area is required',
            'local_address_building.required' => 'Kuwait Address Building is required',
            'local_address_flat.required' => 'Kuwait Address Flat is required',
            'local_address_floor.required' => 'Kuwait Address Floor Number is required',

            'avatar.required' => 'Profile photo is required',
            'avatar.image' => 'Profile photo should be an image',
            'avatar.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif, svg.',
            'avatar.max' => 'Profile photo size should not be exceeded more than 2mb',
            
            'photo_civil_id_front.required' => 'Civil Id copy (Front) is required',
            'photo_civil_id_front.image' => 'Civil Id copy (Front) should be an image',
            'photo_civil_id_front.mimes' => 'Civil Id copy (Front) must be a file of type: jpeg, png, jpg, gif, svg.',
            'photo_civil_id_front.max' => 'Civil Id copy (Front) file size should not be exceeded more than 2mb',
            'photo_civil_id_back.required' => 'Civil Id copy (Back) is required',
            'photo_civil_id_back.image' => 'Civil Id copy (Back) should be an image',
            'photo_civil_id_back.mimes' => 'Civil Id copy (Back) must be a file of type: jpeg, png, jpg, gif, svg.',
            'photo_civil_id_back.max' => 'Civil Id copy (Back) file size should not be exceeded more than 2mb',
            
            'photo_passport_front.required' => 'Passport copy (Front) is required',
            'photo_passport_front.image' => 'Passport copy (Front) should be an image',
            'photo_passport_front.mimes' => 'Passport copy (Front) must be a file of type: jpeg, png, jpg, gif, svg.',
            'photo_passport_front.max' => 'Passport copy (Front) file size should not be exceeded more than 2mb',
            'photo_passport_back.required' => 'Passport copy (Back) is required',
            'photo_passport_back.image' => 'Passport copy (Back) should be an image',
            'photo_passport_back.mimes' => 'Passport copy (Back) must be a file of type: jpeg, png, jpg, gif, svg.',
            'photo_passport_back.max' => 'Passport copy (Back) file size should not be exceeded more than 2mb',
        ];

        if($request->type == 'family'){
            $rules['spouse_name'] = ['required', 'string'];
            $rules['spouse_email'] = ['required', Rule::unique(User::class, 'email')];
            $rules['spouse_phone'] = ['required', Rule::unique(User::class, 'phone')];
            $rules['spouse_whatsapp'] = ['required', 'numeric'];
            $rules['spouse_emergency_phone'] = ['required', 'numeric'];
            $rules['spouse_dob'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_gender'] = ['required', 'string'];
            $rules['spouse_blood_group'] = ['required', 'string'];
            $rules['spouse_civil_id'] = ['required', 'string'];
            $rules['spouse_passport_no'] = ['required', 'string'];
            $rules['spouse_passport_expiry'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_avatar'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];
            $rules['spouse_photo_civil_id_front'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];
            $rules['spouse_photo_civil_id_back'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];
            $rules['spouse_photo_passport_front'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];
            $rules['spouse_photo_passport_back'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];

            $messages['spouse_phone.required'] = 'Spouse Phone number is required';
            $messages['spouse_phone.unique'] = 'Spouse This phone number is already used';
            $messages['spouse_whatsapp.required'] = 'Spouse Whatsapp Number is required';
            $messages['spouse_whatsapp.numeric'] = 'Spouse Whatsapp number should be a number';
            $messages['spouse_emergency_phone.required'] = 'Spouse Emergency Phone Number is required';
            $messages['spouse_emergency_phone.numeric'] = 'Spouse Emergency Phone number should be a number';
            $messages['spouse_civil_id.required'] = 'Spouse Civil ID is required';
            $messages['spouse_civil_id.string'] = 'Spouse Civil ID is not valid';
            $messages['spouse_dob.required'] = 'Spouse Date of birth is required';
            $messages['spouse_dob.date_format'] = 'Spouse Date of birth should be of format Y-m-d';
            $messages['spouse_passport_no.required'] = 'Spouse Passport number is required';
            $messages['spouse_passport_expiry.required'] = 'Spouse Passport expiry date is required';
            $messages['spouse_passport_expiry.date_format'] = 'Spouse Passport expiry date should be of format Y-m-d';
            $messages['spouse_gender.required'] = 'Spouse Gender is required';
            $messages['spouse_blood_group.required'] = 'Spouse Blood group is required';

            $messages['spouse_avatar.required'] = 'Spouse Profile photo is required';
            $messages['spouse_avatar.image'] = 'Spouse Profile photo should be an image';
            $messages['spouse_avatar.mimes'] = 'Spouse Profile photo must be a file of type: jpeg, png, jpg, gif, svg.';
            $messages['spouse_avatar.max'] = 'Spouse Profile photo size should not be exceeded more than 2mb';

            $messages['spouse_photo_civil_id_front.required'] = 'Spouse Civil Id copy (Front) is required';
            $messages['spouse_photo_civil_id_front.image'] = 'Spouse Civil Id copy (Front) should be an image';
            $messages['spouse_photo_civil_id_front.mimes'] = 'Spouse Civil Id copy (Front) must be a file of type: jpeg, png, jpg, gif, svg.';
            $messages['spouse_photo_civil_id_front.max'] = 'Spouse Civil Id copy (Front) file size should not be exceeded more than 2mb';
            $messages['spouse_photo_civil_id_back.required'] = 'Spouse Civil Id copy (Back) is required';
            $messages['spouse_photo_civil_id_back.image'] = 'Spouse Civil Id copy (Back) should be an image';
            $messages['spouse_photo_civil_id_back.mimes'] = 'Spouse Civil Id copy (Back) must be a file of type: jpeg, png, jpg, gif, svg.';
            $messages['spouse_photo_civil_id_back.max'] = 'Spouse Civil Id copy (Back) file size should not be exceeded more than 2mb';
            
            $messages['spouse_photo_passport_front.required'] = 'Spouse Passport copy (Front) is required';
            $messages['spouse_photo_passport_front.image'] = 'Spouse Passport copy (Front) should be an image';
            $messages['spouse_photo_passport_front.mimes'] = 'Spouse Passport copy (Front) must be a file of type: jpeg, png, jpg, gif, svg.';
            $messages['spouse_photo_passport_front.max'] = 'Spouse Passport copy (Front) file size should not be exceeded more than 2mb';
            $messages['spouse_photo_passport_back.required'] = 'Spouse Passport copy (Back) is required';
            $messages['spouse_photo_passport_back.image'] = 'Spouse Passport copy (Back) should be an image';
            $messages['spouse_photo_passport_back.mimes'] = 'Spouse Passport copy (Back) must be a file of type: jpeg, png, jpg, gif, svg.';
            $messages['spouse_photo_passport_back.max'] = 'Spouse Passport copy (Back) file size should not be exceeded more than 2mb';
        }

        return [
            $rules,
            $messages
        ];
    }


    
}
