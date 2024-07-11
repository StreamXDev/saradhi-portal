<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email'
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $input['password'] = Hash::make(Str::random(10));
        $user = User::create($input);
        $user->assignRole(['Member']);
        
        // Sending OTP
        $this->sendOtp($request);

        $success['email'] = $request->email;
        if($user){
            return $this->sendResponse($success, 'User Registered successfully.');
        }
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User logged in successfully.');
        } 
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
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
            $success['token'] = $user->createToken(env('APP_NAME'))->plainTextToken;
            $success['name'] = $user->name;

            return $this->sendResponse($success, 'User login successfully.');
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

        $otp = $user->otp()->firstOrCreate([], [
            'token' => rand(1000, 9999),
        ]);

        $otp->load('authable');

        $user->notify(new SendOtp($otp));

        $success['otp_sent'] = true;

        return $this->sendResponse($success, 'OTP send successfully.');
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
            $success['token'] = $user->createToken(env('APP_NAME'))->plainTextToken;
            $success['name'] = $user->name;

            return $this->sendResponse($success, 'User logged in successfully.');
        }else{
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
        }
    }
}
