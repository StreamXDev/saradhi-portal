<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Country;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MembersController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:profile.view', ['only' => ['showProfile, createDetails']]);
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
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedTo.user', 'requests', 'committees', 'trustee'])->where('user_id' , $user->id)->first();
        $statuses = requestStatusDisplay($user->id);
        $current_status = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
        $idQr = QrCode::size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
        $data = [
            'member' => $member,
            'statuses' => $statuses,
            'current_status' => $current_status,
            'idQr' => $idQr,
            'is_member' => true,
            'profile_completed' => true
        ];
        return $this->sendResponse($data);
    }

    /**
     * Sending Login OTP to email
     * 
     * @return \Illuminate\Http\Response
     */
    /*
    private function sendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')]
        ]);
        $data = $request->all();
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
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
    /*
    public function verifyEmailOtp(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')],
                'otp' => 'bail|required|integer'
            ]);
            $data = $request->all();
            if($validator->fails()){
                return $this->sendError('Validation Error', $validator->errors());     
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
    /*
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
 */
    
    /**
     * Send the form for creating a new resource.
     */
    public function createDetails()
    {
        $countries = Country::with('regions')->where('active', 1)->get();
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = array(
            ['id' => 1, 'name'=>'Male', 'slug' => 'male'], 
            ['id' => 2, 'name' => 'Female', 'slug' => 'female']
        );
        $district_kerala = array(
            ['id' => 1, 'name' => 'Alappuzha', 'slug' => 'alappuzha'],
            ['id' => 2, 'name' => 'Ernakulam', 'slug' => 'ernakulam'],
            ['id' => 3, 'name' => 'Idukki', 'slug' => 'idukki'],
            ['id' => 4, 'name' => 'Kannur', 'slug' => 'kannur'],
            ['id' => 5, 'name' => 'Kasaragod', 'slug' => 'kasaragod'],
            ['id' => 6, 'name' => 'Kollam', 'slug' => 'kollam'],
            ['id' => 7, 'name' => 'Kottayam', 'slug' => 'kottayam'],
            ['id' => 8, 'name' => 'Kozhikkode', 'slug' => 'kozhikkode'],
            ['id' => 9, 'name' => 'Malappuram', 'slug' => 'malappuram'],
            ['id' => 10, 'name' => 'Palakkad', 'slug' => 'palakkad'],
            ['id' => 11, 'name' => 'Pathanamthitta', 'slug' => 'pathanamthitta'],
            ['id' => 12, 'name' => 'Thiruvananthapuram', 'slug' => 'thriuvananthapuram'],
            ['id' => 13, 'name' => 'Thrissur', 'slug' => 'thrissur'],
            ['id' => 14, 'name' => 'Wayanad', 'slug' => 'wayanad'],
            ['id' => 15, 'name' => 'Other', 'slug' => 'other'],
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
            return $this->sendError('Validation Error', $validator->errors(), 403);       
        }
        $input = $request->all();
        $avatarName = 'av'.$user->id.'_'.time().'.jpg';
        Storage::put('public/images/'.$avatarName, base64_decode($input['photo']));
        $input['avatar'] = $avatarName;
        DB::beginTransaction();
        try {
            // Adding member details
            MemberDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'member_unit_id' => $input['member_unit_id'],
                    'civil_id' => $input['civil_id'],
                    'dob' => $input['dob'],
                    'whatsapp' => $input['whatsapp'],
                    'whatsapp_code' => $input['whatsapp_code'],
                    'emergency_phone' => $input['emergency_phone'],
                    'emergency_phone_code' => $input['emergency_phone_code'],
                    'company' => isset($input['company']) ? $input['company'] : null,
                    'profession' => isset($input['profession']) ? $input['profession'] : null,
                    'company_address' => isset($input['company_address']) ? $input['company_address'] : null,
                    'passport_no' => $input['passport_no'],
                    'passport_expiry' => $input['passport_expiry'],
                    'paci' => isset($input['paci']) ? $input['paci'] : null,
                    'sndp_branch' => isset($input['sndp_branch']) ? $input['sndp_branch'] : null,
                    'sndp_branch_number' => isset($input['sndp_branch_number']) ? $input['sndp_branch_number'] : null,
                    'sndp_union' => isset($input['sndp_union']) ? $input['sndp_union'] : null,
                    'completed' => 0
                ]
            );

            
            DB::commit();
            $response = [
                'success' => true,
                'user' => $user,
                'family_request' => $input['type'] === 'family' ? true : false,
                'proof_pending' => true
            ];
            if($input['type'] === 'family'){
                //$response['spouse'] = $spouse_user;
            }
            return $this->sendResponse($response, 'Your member details added successfully.');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed adding member details', $e);
        }
    }

    protected function validationRules($request)
    {
        $rules =  [
            'member_unit_id'    => ['required', Rule::exists(MemberUnit::class, 'id')],
            'phone'             => ['required', Rule::unique(User::class)],
            'calling_code'      => ['required'],
            'whatsapp'          => ['required', 'numeric'],
            'whatsapp_code'     => ['required'],
            'emergency_phone'   => ['required', 'numeric'],
            'emergency_phone_code' => ['required'],
            'gender'            => ['required', 'string'],
            'dob'               => ['required', 'date_format:Y-m-d'],
            'blood_group'       => ['required', 'string'],
            'civil_id'          => ['required', 'string'],
            'passport_no'       => ['required', 'string'],
            'passport_expiry'   => ['required', 'date_format:Y-m-d'],
            'governorate'        => ['required', 'string'],
            'local_address_area'=> ['required', 'string'],
            'type'              => ['required', 'string'],
            'photo'             => ['required']
        ];

        $messages = [
            'member_unit_id.required' => 'Unit is required',
            'member_unit_id.exists' => 'Invalid Unit ID',
            'phone.required' => 'Phone is required',
            'phone.unique' => 'phone already used',
            'calling_code.required' => 'Required',
            'whatsapp.required' => 'Whatsapp is required',
            'whatsapp.numeric' => 'Should be a number',
            'whatsapp_code.required' => 'Required',
            'emergency_phone.required' => 'Emergency contact is required',
            'emergency_phone.numeric' => 'Should be a number',
            'emergency_phone_code.required' => 'Required',
            'gender.required' => 'Gender is required',
            'dob.required' => 'DOB is required',
            'dob.date_format' => 'Should be Y-m-d format',
            'blood_group.required' => 'Blood group is required',
            'civil_id.required' => 'Civil ID is required',
            'civil_id.string' => 'Invalid Civil ID',
            'passport_no.required' => 'Passport no. is required',
            'passport_expiry.required' => 'Expiry date is required',
            'passport_expiry.date_format' => 'Should be Y-m-d format',
            'governorate.required' => 'Governorate is required',
            'local_address_area.required' => 'Area is required',
            'type.required' => 'Membership type is required',
            'photo.required' => 'Photo is required'
        ];

        if($request->type === 'family'){
            $rules['spouse_name'] = ['required', 'string'];
            $rules['spouse_email'] = ['required', Rule::unique(User::class, 'email')];
            $rules['spouse_phone'] = ['required', Rule::unique(User::class, 'phone')];
            $rules['spouse_calling_code'] = ['required'];
            $rules['spouse_whatsapp'] = ['required', 'numeric'];
            $rules['spouse_whatsapp_code'] = ['required'];
            $rules['spouse_emergency_phone'] = ['required', 'numeric'];
            $rules['spouse_emergency_phone_code'] = ['required'];
            $rules['spouse_gender'] = ['required', 'string'];
            $rules['spouse_dob'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_blood_group'] = ['required', 'string'];
            $rules['spouse_civil_id'] = ['required', 'string'];
            $rules['spouse_passport_no'] = ['required', 'string'];
            $rules['spouse_passport_expiry'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_photo'] = ['required'];

            $messages['spouse_name.required'] = 'Name is required';
            $messages['spouse_email.required'] = 'Email is required';
            $messages['spouse_email.unique'] = 'Email already registered';
            $messages['spouse_phone.required'] = 'Phone is required';
            $messages['spouse_phone.unique'] = 'Number already used';
            $messages['spouse_calling_code.required'] = 'Required';
            $messages['spouse_whatsapp.required'] = 'Whatsapp is required';
            $messages['spouse_whatsapp.numeric'] = 'Whatsapp number should be a number';
            $messages['spouse_whatsapp_code.required'] = 'Required';
            $messages['spouse_emergency_phone.required'] = 'Emergency Phone required';
            $messages['spouse_emergency_phone.numeric'] = 'Should be a number';
            $messages['spouse_emergency_phone_code.required'] = 'Required';
            $messages['spouse_gender.required'] = 'Gender is required';
            $messages['spouse_dob.required'] = 'DOB is required';
            $messages['spouse_dob.date_format'] = 'Should be Y-m-d format';
            $messages['spouse_blood_group.required'] = 'Blood group is required';
            $messages['spouse_civil_id.required'] = 'Civil ID is required';
            $messages['spouse_civil_id.string'] = 'Invalid Civil ID';
            $messages['spouse_passport_no.required'] = 'Passport no. is required';
            $messages['spouse_passport_expiry.required'] = 'Expiry date is required';
            $messages['spouse_passport_expiry.date_format'] = 'Should be Y-m-d format';
            $messages['spouse_photo.required'] = 'Photo is required';
        }

        return [
            $rules,
            $messages
        ];
    }


    
}
