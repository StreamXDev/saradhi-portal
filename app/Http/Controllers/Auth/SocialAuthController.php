<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }
          
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function googleCallback()
    {
        try {
        
            $providerUser = Socialite::driver('google')->user();
            $user = User::where('email', $providerUser->email)->first();
         
            if($user){
                Auth::login($user);
            }
            return redirect()->intended('home');
        
        } catch (Exception $e) {
            dd($e->getMessage()); //TODO: add exception handler
        }
    }
}
