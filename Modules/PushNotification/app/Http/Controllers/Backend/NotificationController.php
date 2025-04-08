<?php

namespace Modules\PushNotification\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\PushNotification\Models\PnDelivery;
use Modules\PushNotification\Models\PnDevice;
use Modules\PushNotification\Models\PnMessage;

class NotificationController extends Controller
{
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
            if($user->id == 1){
                $devicesSent = $this->notify($user->id, $message->title,$message->description);
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
        }
        return redirect('/admin/push-notification')->with('success', 'Notification sent successfully');

    }


    /**
     * Send User notification 
     */
    public function sendUserPushNotification($user,$title,$body=null,$link=null,$image=null)
    {
        
        //return redirect()->back()->with('success', 'Message Sent');   
    }

    /**
     * Send Notification function (private)
    */
    private function notify($user=null,$title,$description=null,$link=null,$image=null){
        $devices = PnDevice::where('user_id', $user)->get();
        $devicesSent = [];
        foreach($devices as $device){
            //send notification to device
            $sent = true;

            if($sent){
                $devicesSent[] = $device;
            } 
        }
        
        return $devicesSent;
    }
}