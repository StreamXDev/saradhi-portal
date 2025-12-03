<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberTransfer extends BaseController
{

    public function getUser($id)
    {
        $user = User::where('id', $id)->first();
        $data = [
            'users' => $user
        ];
        return $this->sendResponse($data, 'User.');
    }

    public function getUsersAfterId($id)
    {
        $users = User::where('id', '>', $id)->take(5)->get();
        $data = [
            'users' => $users
        ];
        return $this->sendResponse($data, 'All users.');
    }
}
