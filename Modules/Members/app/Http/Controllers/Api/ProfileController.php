<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Imports\Models\Member;
use Modules\Members\Models\MembershipRequest;

class ProfileController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedTo.user', 'requests', 'committees', 'trustee'])->where('user_id' , $user->id)->first();
        $statuses = requestStatusDisplay($user->id);
        $current_status = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
        $idQr = QrCode::size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
        $data = [
            'member' => $member,
            'statuses' => $statuses,
            'current_status' => $current_status,
            'idQr' => $idQr,
            'is_member' => true,
            'profile_completed' => true
        ];
        return $this->sendResponse($data);
    }
}
