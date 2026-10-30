<?php

namespace Modules\PushNotification\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\PushNotification\Models\PnDelivery;
use Modules\PushNotification\Models\PnDevice;
use Modules\PushNotification\Models\PnMessage;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:notification.create|notification.send', ['only' => ['index','storeAndSend']]);
    }
    
    /**
     * Notification massage 
     */
    public function index()
    {
        $menuParent = 'notification';
        return view('pushnotification::backend.index', compact('menuParent'));
    }
    
    /**
     * Store Notification message and send
     * Method: POST
     */
    public function storeAndSend(Request $request){
        $logged_user = Auth::user();
        $users = User::get();

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->with('error', 'Some fields are not valid');
        }

        $message = PnMessage::create([
            'title' => $input['title'],
            'description' => $input['description'],
            'image' => isset($input['image']) ? $input['image'] : null,
            'link' => isset($input['link']) ? $input['link'] : null,
            'created_by' => $logged_user->id,
        ]);

        foreach($users as $key => $user){
            $devicesSent = $this->notify($user->id, $message->title, $message->description, $message->id);
            if(count($devicesSent) > 0){
                PnMessage::where([
                    'id' => $message->id
                ])->update([
                    'total_users' => $users->count(),
                    'total_sent' => $message->total_sent+1
                ]);
                foreach($devicesSent as $device){
                    PnDelivery::create([
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'message_id' => $message->id,
                    ]);
                }
            }
        }
        return redirect('/admin/push-notification')->with('success', 'Notification sent successfully');

    }

    /**
     * Send Notification function (private)
    */
   /*
    private function notify($user=null, $title, $description=null, $notificationId=null, $link=null, $image=null){
        $devices = PnDevice::where('user_id', $user)->get();
        $devicesSent = [];
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

        foreach($devices as $device){
            //send notification to device
            
            $data = [
                "message" => [
                    "token" => $device->token,
                    "notification" => [
                        "title" => $title,
                        "body" => $description,
                    ],
                    "data" => [
                        "screen" => "notification",
                        "id" => "".$notificationId, // converting to string
                    ],
                ]
            ];
            
            
            $payload = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
            curl_setopt($ch, CURLOPT_HEADER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            $devicesSent[] = $device;

            if ($err) {
                $devicesSent[]['error'] = $response;
            }
        }
        
        return $devicesSent;
    }
        */

    private function notify($user=null, $title, $description=null, $notificationId=null, $link=null, $image=null) {
        $devices = PnDevice::where('user_id', $user)->get();
        
        // Early exit if no devices found to save API calls
        if ($devices->isEmpty()) {
            return [];
        }

        $projectId = env('FIREBASE_PROJECT_NUMBER'); 
        $credentialsFilePath = Storage::path('json/service-account.json');
        $credentials = json_decode(File::get($credentialsFilePath), true);

        // 1. Handle the Server Clock Drift
        // Adjust this integer based on how far off your server clock is. 
        // Example: If the token still fails, try changing it to -120 or 60.
        $offsetSeconds = 120; 
        $googleTime = time() + $offsetSeconds;

        $payload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $googleTime,
            'exp' => $googleTime + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];

        // 2. Generate and exchange the JWT manually
        $jwt = JWT::encode($payload, $credentials['private_key'], 'RS256');

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if ($tokenResponse->failed()) {
            \Log::error('FCM OAuth Error: ' . $tokenResponse->body());
            return ['error' => 'Google Auth failed. Check offset or credentials.'];
        }

        $access_token = $tokenResponse->json('access_token');
        $devicesSent = [];

        // 3. Send notifications using Laravel's Http facade instead of raw cURL
        foreach ($devices as $device) {
            $data = [
                "message" => [
                    "token" => $device->token,
                    "notification" => [
                        "title" => $title,
                        "body" => $description,
                    ],
                    "data" => [
                        "screen" => "notification",
                        "id" => (string) $notificationId,
                    ],
                ]
            ];

            $fcmResponse = Http::withHeaders([
                'Authorization' => "Bearer {$access_token}",
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $data);

            if ($fcmResponse->successful()) {
                $devicesSent[] = $device;
            } else {
                Log::error("FCM Send Error for token {$device->token}: " . $fcmResponse->body());
                $devicesSent[] = [
                    'device' => $device->token,
                    'error' => $fcmResponse->json()
                ];
            }
        }
        
        return $devicesSent;
    }


    /**
     * Send User notification 
     */
    public function sendTestNotification()
    {
        $message = PnMessage::latest()->first();
        $user = 1;
        
        /*
        $response = $this->notify($user, $message->title, $message->description, $message->id);
        dd($response);
        */
        
        $devices = [
            [
                'token' => 'dCx3b4bSwUBitkxfTxkETr:APA91bHcIXaQK1_QEUR8YtkQIw3WOrRElxviAoTFywflHSZB7xldkg7gtN1g6mNSw8eXeKUZzq_6kUXrufh8RJcQquBtPjf4YkoH9aFotjZA7tnxKN3WSHc'
            ],
        ];

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

        foreach($devices as $device){
            //send notification to device
            
            $data = [
                "message" => [
                    "token" => $device['token'],
                    "notification" => [
                        "title" => $message->title,
                        "body" => $message->description,
                    ],
                    "data" => [
                        "screen" => "notification",
                        "id" => "".$message->id,
                    ],
                ]
            ];
            $payload = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
            curl_setopt($ch, CURLOPT_HEADER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            dd($response);
        }
        
        //return redirect('/admin/push-notification')->with('success', 'Notification sent successfully to '.count($devicesSent). 'devices');
    }
}