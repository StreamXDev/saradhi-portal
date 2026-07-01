<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Members\Repositories\MembershipRequestRepository;
use Modules\Members\Services\MemberRequestService;

class ApiMembershipController extends BaseController
{
    
    public function __construct(
        protected MemberRequestService $requestService,
        protected MembershipRequestRepository $requestRepository
    ){}
    /**
     * Updating membership request status
     */
    public function changeStatus(Request $request)
    {
        $input = $request->all();
        $user = User::where('email', $input['email'])->first();
        $status = $this->requestRepository->getStatusEnumBySlug($input['current_stage']);
        $data = [
            'user_id' => $user->id,
            'current_status_id' => $status->id,
            'action' => $input['action'],
            'remark' => $input['remark'],
        ];
        $loggedAs = User::where('email', $input['loggedAs'])->first();
        $updated = $this->requestService->changeStatus($data, $loggedAs);
        return $this->sendResponse($data, $updated['message']);
    }

    /**
     * Confirming membership
     */
    public function confirmMembership(Request $request)
    {
        $input = $request->all();
        $user = User::where('email', $input['email'])->first();
        $data = [
            'user_id' => $user->id,
            'remark' => $input['remark'],
            'mid' => $input['mid'],
            'start_date' => $input['start_date']
        ];
        $loggedAs = User::where('email', $input['loggedAs'])->first();
        $updated = $this->requestService->confirmRequest($data, $loggedAs);
        return $this->sendResponse($data, $updated['message']);
    }
}
/**
 * user_id = get user email and find user id
 * current_status_id = get status name, and find slug from enum
 * action = change, reject, revise
 * remark = notes
 * mid = input
 * start_date = input 
 */
