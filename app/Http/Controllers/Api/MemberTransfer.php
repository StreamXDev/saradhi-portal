<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDependent;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;

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

    public function getMember($id)
    {
        $user = User::where('id', $id)->first();
        $member = Member::where('user_id', $user->id)->first();
        $membership = Membership::where('user_id', $user->id)->first();
        $memberDetails = MemberDetail::where('user_id', $user->id)->first();
        $localAddress = MemberLocalAddress::where('user_id', $user->id)->first();
        $permanentAddress = MemberPermanentAddress::where('user_id', $user->id)->first();
        $membership_request = MembershipRequest::where('user_id', $user->id)->get();
        $relations = MemberRelation::where('member_id', $member->id)->get();

        // Reducing avatar image size
        if($user->avatar){
            $source_image_path = storage_path('app/public/images/'.$user->avatar);
            $destination_image_path = storage_path('app/public/images/'.$user->avatar);
            list($width, $height) = getimagesize($source_image_path);
            $new_width = 300;
            $new_height = 300;
            $new_image = imagecreatetruecolor($new_width, $new_height);
            $source_image = imagecreatefromjpeg($source_image_path);
            imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($new_image, $destination_image_path, 90);
            $source_image = null;
            $new_image = null;
        }

        foreach($relations as $relation){
            if($relation->related_member_id){
                $relation->type = 'user';
                $relative_member = Member::where('id', $relation->related_member_id)->first();
                $relation->relative = User::where('id', $relative_member->user_id)->first();
            }else if($relation->related_dependent_id){
                $relation->type = 'dependent';
                $relation->relative = MemberDependent::where('id', $relation->related_dependent_id)->first();
            }
        }
        $member->relations = $relations;
        $data = [
            'user' => $user,
            'member' => $member,
            'membership' => $membership,
            'membership_request' => $membership_request,
            'member_details' => $memberDetails,
            'local_address' => $localAddress,
            'permanent_address' => $permanentAddress
        ];
        return $this->sendResponse($data, 'Member.');
    }
}
