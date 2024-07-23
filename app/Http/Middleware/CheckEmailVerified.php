<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailVerified
{
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if(!$user->hasVerifiedEmail()){
            if ($request->is('api/*')) {
                $response = [
                    'success' => false,
                    'email_verified' => false,
                    'message' => 'Email not verified',
                ];
                return response()->json($response, 401);
            }else{
                return redirect('/member/verify_email_otp?name='.$user->name.'&email='.$user->email);
            }
        }

        return $next($request);
    }
}
