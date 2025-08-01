<?php

namespace Modules\PushNotification\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Modules\PushNotification\Models\PnMessage;

class NotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = PnMessage::orderBy('created_at', 'desc')->paginate(15);
        $data = [
            'notifications' => $notifications
        ];
        return $this->sendResponse($data);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $notification = PnMessage::where('id',$id)->first();
        return $this->sendResponse($notification);
    }

    /**
     * Send User notification 
     */
    public function sendTestNotification()
    {
        $message = PnMessage::latest()->first();
        $user = 1;
        $notification = $this->notify($user, $message->title, $message->description, $message->id);
        return $this->sendResponse($notification);
    }
}
