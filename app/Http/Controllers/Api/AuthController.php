<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Notifications\SendOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'email' => 'required|email|unique'
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        //$user = User::create($input);
        
        //$this->sendOtp($request->email);
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
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
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
                $user->update([
                    'email_verified_at' => now()
                ]);
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
