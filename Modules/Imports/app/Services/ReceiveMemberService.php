<?php

namespace Modules\Imports\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ReceiveMemberService
{
    
    private $api;
    private $accessToken;
    private $headers;
    private $thisUser;

    public function __construct(){
        $this->api = env('NEW_PORTAL_API').'api/';
        // Generating access token
        if(!session()->has('transfer_token')){
            $this->login();
        }
        $this->accessToken = Session::get('transfer_token');
        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
        $this->thisUser = Auth::user();
    }


    /**
     * Login to new portal
     */
    private function login(){
        $response = Http::post($this->api.'auth/login', [
            'email' => 'shanoob.sekhar@gmail.com',
            'password' => 'abc@123',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            session(['transfer_token' => $data['data']['token']]);
        }
    }


    // Fetching User Details
    public function createMember(array $data)
    {
        $newUserId = $data['user_id'];
        $newUser = $this->getUserDetails($newUserId);

        
        /**
         * request user basic details from new portal using the user id
         * check email exists
         * if no, create a new user, $user = $this->getUserDetails()
         * Get user details, member details, membership details, request details using the newUserId,
         * save all data 
         */
    }

    public function getUserDetails(int $userId)
    {
        try {
            $response = Http::withHeaders($this->headers)->get($this->api.'migration/get/user', ['user_id' => $userId]);
            if($response->ok()){
                $response = $response->json();
                return $response['data']['user'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        
    }
}
