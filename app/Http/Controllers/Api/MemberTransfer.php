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
        /**
         * if member->id relations
         * if related member id,
         * get user from related member id
         */
        $relations = MemberRelation::where('member_id', $member->id)->get();
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
            'member_details' => $memberDetails,
            'local_address' => $localAddress,
            'permanent_address' => $permanentAddress
        ];
        return $this->sendResponse($data, 'Member.');
    }
}
