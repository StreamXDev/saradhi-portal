<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberTransfer extends BaseController
{

    public function getUsersAfterId($id)
    {
        $users = User::where('id', '>', $id)->take(5)->get();
        $data = [
            'users' => $users
        ];
        return $this->sendResponse($data, 'All users.');
    }
}
