<?php

namespace Modules\Members\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemberRegisterService
{

    /**
     * Transfer user to new portal
     */
    public function transferCreateUser(Request $data)
    {
        try {
            if(!env('NEW_PORTAL_SYNC')){
                return;
            }
            $api = env('NEW_PORTAL_API').'migration/createUser';
            Http::post($api, $data);
            
            return;
        } catch (\Exception $e) {
            return;
        }
    }
    
    /**
     * Transfer membership request to new api
     */
    public function transferCreateMember(array $data)
    {
        try {
            if(!env('NEW_PORTAL_SYNC')){
                return;
            }
            $api = env('NEW_PORTAL_API').'migration/createMember';
            $response = Http::post($api, $data);
            if($response->ok()){
                return;
            }
        } catch (\Exception $e) {
            return; 
        }
    }
}
