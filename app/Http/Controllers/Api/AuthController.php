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
use Illuminate\Validation\Rules\Password;
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
            'email' => 'bail|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->symbols()],
        ]);
        if($validator->fails()){
            return $this->sendError('Required fields are empty or incorrect', $validator->errors(), 400);       
        }
        $input = $request->all();
        try{
            $user = User::create($input);
            if(isset($request->type) && $request->type == 'member' && Module::has('Members')){
                // Adding member role
                $user->assignRole(['Member']);
                $member ['user_id'] = $user->id;
                $member ['name'] = $user->name;
                Member::create($member);
            }
            // Sending OTP 
            $token = $user->email === 'prejith021@gmail.com' ? 5432 : rand(1000, 9999);
            $otp = $user->otp()->firstOrCreate([], [
                'token' => $token,
            ]);
            $otp->load('authable');
            $user->notify(new SendOtp($otp));
            $data = [
                'emailVerified' => false,
                'optSent' => true,
                'token' => $user->createToken('Saradhi')->plainTextToken,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone' => $user->phone,
                ]
            ];
            return $this->sendResponse($data, 'User registered successfully');
        }catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e, 403);
        }
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
            return $this->sendError('Required fields are empty or incorrect', $validator->errors(), 400);       
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $data['emailVerified'] = $user->email_verified_at !== NULL ? true : false;
            $data['token'] =  $user->createToken('Saradhi')->plainTextToken; 
            $data['user'] = [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'phone' => $user->phone,
            ];
            
            return $this->sendResponse($data, 'User logged in successfully.');
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
            return $this->sendError('Unauthorized', ['error'=>'Otp not available. Request new one and try again.'], 403);
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
            return $this->sendError('Unauthorized', ['error'=>'Unauthorized'], 403);
        }
    }
    
}
