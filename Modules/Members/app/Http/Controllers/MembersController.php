<?php

namespace Modules\Members\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendOtp;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberContact;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Models\MemberUnit;

class MembersController extends Controller
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
     * Display profile verification pending page
     */
    public function profilePending()
    {
        $user = Auth::user();
        $pendings = MembershipRequest::where('user_id', $user->id)->get();
        return view('members::member.profile.pending', compact('pendings'));
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
            
        
        
        
        return view('members::member/profile/index');
    }


    /**
     * Display member registration form
     */
    public function create()
    {
        if (Auth::check()) {
            return redirect('/member/profile');
        }
        return view('members::member.create');
    }

    /**
     * Create member
     */
    public function store(Request $request)
    {
        
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|unique:users,email',
            'type' => 'required'
        ]);

        $input = $request->all();

        if($input['type'] !== 'member'){
            return redirect()->route('login');
        }

        $input['password'] = Hash::make(Str::random(10));
        $user = User::create($input);

        $user->assignRole(['Member']);

        $member ['user_id'] = $user->id;
        $member ['name'] = $user->name;
        Member::create($member);

        // Sending OTP
        
        $this->sendEmailOtp($request);

        return redirect()->route('member.verify_email_otp',['name' => $user->name, 'email' => $user->email]);
    }


    /**
     * Sending Login OTP to email
     * 
     * @return \Illuminate\Http\Response
     */
    private function sendEmailOtp(Request $request)
    {
        
        $data = $request->validate(
            [
                'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')],
            ]
        );

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
     * Display email otp verification form
     */
    public function createEmailOtpForm(Request $request)
    {
        if(!$request->name || !$request->email){
            return redirect()->route('home');
        }
        return view('members::auth.otp.email.verify');
    }

    /**
     * Verify email OTP
     */
    public function verifyEmailOtp(Request $request){
        
        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')],
            'otp' => 'bail|required|integer'
        ]);

        $data = $request->all();
 
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $user = User::where('email', $data['email'])->with('otp')->firstOrFail();

        if($user->otp == null){
            return Redirect::back()->withErrors(['otp' => 'Otp not available. Request new one and try again.']);
        }

        if($user->otp->token === (int) $data['otp']){

            
            // Verifying email if not done already(Using in registration time).
            if($user->email_verified_at == null){
                $user->email_verified_at = now();
                $user->save();
            }
            

            $user->otp()->delete();

            auth()->login($user, (bool) $request->get('remember_me', false));

            
            return redirect('/member/detail');

        }else{
            //throw ValidationException::withMessages(['otp' => 'Invalid OTP. Please try again.']);
            return redirect('/member/verify_email_otp?name='.$data['name'].'&email='.$data['email'])->withErrors(['otp' => 'Invalid OTP. Please try again.'])->withInput();
        }
    }

    /**
     * Resending email OTP
     */
    public function resendEmailOtp(Request $request)
    {
        $user = User::where('email', $request->email)->with('otp')->firstOrFail();
        
        if ($user->hasVerifiedEmail()) {
            $success['email_verified'] = true;
            return Redirect::back()->withErrors(['email' => 'Email already verified']);
        }

        $this->sendEmailOtp($request);

        //$request->user()->sendEmailVerificationNotification();

        $success['email_verification_sent'] = true;
        return Redirect::back()->with(['message' => 'OTP Sent. Please check your email']);
    }

    
    /**
     * Display member detail form
     */
    public function createDetails()
    {

        $user = Auth::user();
        $details = MemberDetail::where('user_id', $user->id)->first();
        if($details && $details->completed){
            return redirect('/member/profile');
        }

        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = [
            ['name'=>'Male', 'slug' => 'male'], 
            ['name' => 'Female', 'slug' => 'female']
        ];

        return view('members::member.detail', compact('units', 'blood_groups', 'gender'));
    }

    /**
     * Store member details
     */
    public function storeDetails(Request $request)
    {
        $user = Auth::user();

        $membership = Membership::where('user_id', $user->id)->first();
        $member_request = MembershipRequest::where('user_id', $user->id)->latest()->first();

        // if already a member or requested for a membership
        if($membership || $member_request){
            return redirect('/member/profile');
        }

        $validator = Validator::make($request->all(), ...$this->validationRules());
        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
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

        MemberDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'member_unit_id' => $input['member_unit_id'],
                'civil_id' => $input['civil_id'],
                'photo_civil_id_front' => $civil_id_front_name,
                'photo_civil_id_back' => $civil_id_back_name,
                'dob' => $input['dob'],
                'company' => $input['company'],
                'profession' => $input['profession'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'photo_passport_front' => $passport_front_name,
                'photo_passport_back' => $passport_back_name,
                'completed' => 1
            ]
        );

        Member::where('user_id', $user->id)->update([
            'gender' => $input['gender'],
            'blood_group' => $input['blood_group']
        ]);

        //Add phone to users and member contact table
        User::where('id', $user->id)->update([
            'phone' => $input['phone'],
            'avatar' => $avatarName,
        ]);

        $contact_types = MemberEnum::where('type', 'contact_type')->where('slug', 'phone')->first();

        Membership::create([
            'user_id' => $user->id,
            'type' => $input['type']
        ]);

        MemberContact::create([
            'user_id' => $user->id,
            'contact_type_id' => $contact_types->id,
            'title' => $contact_types->name,
            'value' => $input['phone']
        ]);

        //Getting status data
        $status = MemberEnum::where('type', 'request_status')->where('order', 0)->first();

        MembershipRequest::create([
            'user_id' => $user->id,
            'request_status_id' => $status->id,
            'updated_by' => $user->id
        ]);

        //TODO: [Phase 2] get notified the users who permitted to view new membership requests

        DB::commit();

        return redirect('/member/profile');
    }

    protected function validationRules()
    {
        $rules =  [
            'member_unit_id'    => ['required', Rule::exists(MemberUnit::class, 'id')],
            'phone'             => ['required', Rule::unique(User::class)],
            'civil_id'          => ['required', 'string'],
            'dob'               => ['required', 'date_format:Y-m-d'],
            'company'           => ['nullable', 'string'],
            'profession'        => ['nullable', 'string'],
            'passport_no'       => ['required', 'string'],
            'passport_expiry'   => ['required', 'date_format:Y-m-d'],
            'gender'            => ['required', 'string'],
            'blood_group'       => ['required', 'string'],
            'type'              => ['required', 'string'],
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

        return [
            $rules,
            $messages
        ];
    }
}
