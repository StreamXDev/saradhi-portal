<?php

namespace Modules\Members\Services;

use Illuminate\Support\Facades\Http;

class MemberRegisterService
{

    /**
     * Transfer membership request to new api
     */
    public function transferInit(array $data)
    {
        try {
            if(!env('NEW_PORTAL_SYNC')){
                return;
            }
            $api = env('NEW_PORTAL_API').'migration/createUser';
            
            $response = Http::timeout(3)->post($api, $data);

            return;
        } catch (\Exception $e) {
            // Fail-open strategy: if remote server is down, let it pass and verify later
            // Remove the return statement if you prefer strict fail-closed security
            return; 
        }
    }
}
