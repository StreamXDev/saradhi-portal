<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\Member;
use Modules\Members\Models\MembershipRequest;

class ProfileController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedMember.user', 'relations.relatedMember.membership', 'relations.relatedMember.details', 'requests', 'committees', 'trustee'])->where('user_id' , $user->id)->first();
        $statuses = requestStatusDisplay($user->id);
        $current_status = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
        $pending_approval = $current_status && $current_status->request_status->slug === 'confirmed' ? false : true;

        //Member ID
        $idQr = QrCode::size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
        $member->membership->qrCode = 'data:image/svg+xml;base64, ' . base64_encode($idQr);
        $member->user->avatar = url('storage/images/'. $member->user->avatar);

        if($member->relations){
            foreach($member->relations as $key => $relative){
                $member->relations[$key]->relatedMember->user->avatar = url('storage/images/'. $member->relations[$key]->relatedMember->user->avatar);
                if($relative->relatedMember->active){
                    $spouseIdQr = QrCode::size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedMember->name,  'Membership ID' => $member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $member->relations[$key]->relatedMember->details->civil_id]));
                    $member->relations[$key]->relatedMember->membership->qrCode = 'data:image/svg+xml;base64, ' . base64_encode($spouseIdQr);
                }
            }
        }
        
        $data = [
            'member' => $member,
            'statuses' => $statuses,
            'is_member' => true,
            'profile_completed' => true,
            'active_membership' => $member->membership->status === 'active' ? true : false,
            'pending_approval' => $pending_approval,
            'current_status' => $current_status,
        ];
        return $this->sendResponse($data);
    }
}
