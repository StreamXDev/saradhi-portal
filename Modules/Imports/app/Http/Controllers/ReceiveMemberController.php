<?php

namespace Modules\Imports\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Imports\Jobs\ReceiveMemberJob;
use Modules\Imports\Services\ReceiveMemberService;

class ReceiveMemberController extends BaseController
{
    
    public function __construct(
        protected ReceiveMemberService $receiveMemberService
    ){}

    /** Checking email exists */
    public function checkEmailExists(Request $request)
    {
        $emailExists = false;
        $email = $request->query('email');
        if(!$email){
            return $this->sendError('email is required', ['email' => null]);
        }
        $emailExists = User::where('email', $email)->first();
        $data = [
            'emailExists' => $emailExists ? true : false,
            'user' => $emailExists
        ];
        return $this->sendResponse($data, 'Success');
    }

    /**
     * Display a listing of the resource.
     */
    public function receiveNewUserId(Request $request)
    {
        try {
            $data = $request->all();
            
            ReceiveMemberJob::dispatch($data);
            
            return $this->sendResponse($data, 'Request received and queued for processing.');
            
        } catch (\Exception $e) {
            return $this->sendError('Failed to create member.', $e);
        }
    }
}
