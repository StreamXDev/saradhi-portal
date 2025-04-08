<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FcmController extends BaseController
{
    
    public function updateDeviceToken(Request $request)
    {
        
        $user = Auth::user();
        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'os' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()); 
        }


        if($input['os'] === 'ios'){
            $user->update(['fcm_token_ios' => $input['token']]);
        }else if($input['os'] === 'android'){
            $user->update(['fcm_token_android' => $input['token']]);
        }

        $data = [
            'tokenSent' => true,
        ];

        return $this->sendResponse($data, 'Device token updated successfully for id:'.$user->id);
    }

    public function sendFcmNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $fcm_ios = $user->fcm_token_ios;
        $fcm_android = $user->fcm_token_android;


        if (!$fcm_ios && !$fcm_android) {
            //return response()->json(['message' => 'User does not have a device token'], 400);
        }

        $title = $request->title;
        $description = $request->body;
        $projectId = env('FIREBASE_PROJECT_NUMBER'); 

        $credentialsFilePath = Storage::path('json/service-account.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        if($fcm_ios){
            $data = [
                "message" => [
                    "token" => $fcm_ios,
                    "notification" => [
                        "title" => $title,
                        "body" => $description,
                    ],
                ]
            ];
            $payload = json_encode($data);
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
        }
        if($fcm_android){
            $data = [
                "message" => [
                    "token" => $fcm_android,
                    "notification" => [
                        "title" => $title,
                        "body" => $description,
                    ],
                ]
            ];
            $payload = json_encode($data);
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
        }


        if ($err) {
            return response()->json([
                'message' => 'Curl Error: ' . $err
            ], 500);
        } else {
            return response()->json([
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ]);
        }
    }
}