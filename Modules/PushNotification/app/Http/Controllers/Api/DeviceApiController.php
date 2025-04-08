<?php

namespace Modules\PushNotification\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\PushNotification\Models\PnDevice;

class DeviceApiController extends BaseController
{
    /**
     * Add/Update device
     */
    public function storeDevice(Request $request){
        $logged_user = Auth::user();
        $input = $request->all();

        $request->validate([
            'token' => 'required|string',
            'os' => 'required|string'
        ]);

        $user = User::where('id', $logged_user->id)->first();

        PnDevice::updateOrCreate([
            'user_id' => $user->id,
            'token' => $input['token']
        ],[
            'user_id' => $user->id,
            'device' => isset($input['device']) ? $input['device'] : null,
            'os' => $input['os'],
            'token' => $input['token'],
            'last_active' => now()
        ]);

        $response = [
            'success' => true,
        ];
        return $this->sendResponse($response, 'Device added successfully.');
    }
}
