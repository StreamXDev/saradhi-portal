<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;

class EmailVerificationController extends BaseController
{
    use VerifiesEmails;

    /**
    * Show the email verification notice.
    *
    */
    public function show()
    {
        //
    }

    /**
    * Mark the authenticated user’s email address as verified.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function verify(Request $request) {
        $userID = $request['id'];
        $user = User::findOrFail($userID);
        $date = now();
        $user->email_verified_at = $date; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature 
        $user->save();

        $success['email_verified'] = true;
        return $this->sendResponse($success, 'Email verified.');
    }

    /**
    * Resend the email verification notification.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            $success['email_verified'] = true;
            return $this->sendResponse($success, 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        $success['email_verification_sent'] = true;
        return $this->sendResponse($success, 'Sent email verification code.');

    }

}
