<?php

namespace Modules\Imports\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
     * Creating new user - Getting data from new portal
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|confirmed',
        ]);
        if($validator->fails()){
            return $this->sendError('Required fields are empty or incorrect', $validator->errors(), 400);       
        }
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return $this->sendResponse($newUser, 'User created successfully.');
    }

    /**
     * Starting member data fetch job
     */
    public function initMember(Request $request)
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
