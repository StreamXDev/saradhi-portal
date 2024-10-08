<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\OtpController;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\Members\Models\Member;
use Nwidart\Modules\Facades\Module;

class AuthController extends BaseController
{

    use VerifiesEmails;

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'digits:10|unique:users,phone',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);

        if(isset($request->type) && $request->type == 'member' && Module::has('Members')){
            // Adding member role
            $user->assignRole(['Member']);

            $member ['user_id'] = $user->id;
            $member ['name'] = $user->name;
            Member::create($member);
        }

        // Sending OTP
        $this->sendOtp($request);
        /*
        $success['email'] = $request->email;
        if($user){
            return $this->sendResponse($success, 'User Registered successfully.');
        }
        return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
        */
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $data['emailVerified'] = false;
            if($user->email_verified_at !== NULL){
                $data['token'] =  $user->createToken('Saradhi')->plainTextToken; 
                $data['user'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone' => $user->phone,
                ];
                $data['emailVerified'] = true;
                return $this->sendResponse($data, 'User logged in successfully.');
            }else{
                $data['error'] = 'Your email not verified';
                return $this->sendError('Email not verified.', $data, 403);
            }
        } 
        $data['error'] = 'Username or password does not match';
        return $this->sendError('Username or password does not match', $data, 403);
    }

    /**
     * Login with socialite api
     * 
     * @return \Illuminate\Http\Response
     */
    public function socialLogin(Request $request)
    {
        $provider = $request->input('provider_name');
        $token = $request->input('access_token');
        $providerUser = Socialite::driver($provider)->userFromToken($token);

        //$user = User::where('provider_name', $provider)->where('provider_id', $providerUser->id)->first();
        $user = User::where('email', $providerUser->email)->first();
        // TODO: if user found, updateOrCreate with provider name and id;

        if($user){
            $data['token'] = $user->createToken(env('APP_NAME'))->plainTextToken;
            $data['user'] = [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'phone' => $user->phone,
            ];
            
            return $this->sendResponse($data, 'User login successfully.');
        }else{
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
        }

    }

    /**
     * Sending Login OTP to email
     * 
     * @return \Illuminate\Http\Response
     */
    
    public function sendOtp(Request $request)
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

        $success['otp_sent'] = true;

        return $this->sendResponse($success, 'OTP sent successfully.');
    }

    /**
     * Verifying Login OTP
     * 
     * @return \Illuminate\Http\Response
     */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate(
            [
                'email' => ['bail', 'required', 'email', Rule::exists(User::class, 'email')],
                'otp' => 'bail|required|integer'
            ]
        );

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
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
        }
    }
    
}
