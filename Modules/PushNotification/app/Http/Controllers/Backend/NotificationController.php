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
     * Send User notification 
     */
    public function sendTestNotification()
    {
        $message = PnMessage::latest()->first();
        $user = 1;
        $this->notify($user, $message->title, $message->description, $message->id);
    }

    /**
     * Send Notification function (private)
    */
    private function notify($user=null, $title, $description=null, $notificationId=null, $link=null, $image=null){
        $devices = PnDevice::where('user_id', $user)->get();
        $devicesSent = [];
        foreach($devices as $device){
            //send notification to device
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

            $data = [
                "message" => [
                    "token" => $device->token,
                    "notification" => [
                        "title" => $title,
                        "body" => $description,
                    ],
                    "data" => [
                        "screen" => 'notification',
                        "id" => $notificationId
                    ]
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

            $devicesSent[] = $device;

            if ($err) {
                $devicesSent[]['error'] = $response;
            }
        }
        
        return $devicesSent;
    }
}