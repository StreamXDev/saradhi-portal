<?php

namespace Modules\Members\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Members\Models\Member;
use Modules\Members\Models\Membership;
use Modules\Members\Repositories\MembershipRequestRepository;

class MemberRequestService
{
    
    public function __construct(
        protected MembershipRequestRepository $requestRepository
    ){}

    /**
     * Changing request status 
     * */    
    public function changeStatus(array $data)
    {
        try {
            $loggedUser = Auth::user();
            $active_request = $this->requestRepository->getRequestByIds($data['user_id'], $data['current_status_id']);
            DB::beginTransaction();
            if($active_request){
                $current_status = $this->requestRepository->getStatusEnumById($data['current_status_id']);
                $current_status_order = $current_status->order;
        
                if($data['action'] == 'revise'){
                    if($current_status->slug == 'rejected'){
                        $previous_status_id = $active_request->rejected;
                        $prevRequestStatus = $this->requestRepository->getRequestByIds($data['user_id'], $previous_status_id);
                        $prevRequestStatus->update(['checked' => 0]);
                    }else{
                        $previous_status_order = $current_status_order - 1;
                        $previous_status = $this->requestRepository->getStatusEnumByOrder($previous_status_order);
                        $prevRequestStatus = $this->requestRepository->getRequestByIds($data['user_id'], $previous_status->id);
                        $prevRequestStatus->update(['checked' => 0]);
                    }
                    $active_request->delete();
                    return ['message' => 'Request has been updated successfully.'];
                }
        
                if($data['action'] == 'reject' && $current_status->slug == 'rejected'){
                    throw new \Exception('The request already rejected.');
                }
        
                $next_status_order = $current_status_order + 1;
                if($current_status->order == 0){ //if current status is rejected
                    $old_status_order = $this->requestRepository->getRequestByIds($data['user_id'], $current_status->rejected);
                    $next_status_order = $old_status_order + 1;
                }
        
                $active_request->checked = 1;
                $active_request->save();
        
                $rejected = null;
                if($data['action'] == 'reject'){
                    $new_status = $this->requestRepository->getStatusEnumByOrder(0);
                    $rejected = $data['current_status_id'];
                }else{
                    $new_status = $this->requestRepository->getStatusEnumByOrder($next_status_order);
                }
                $this->requestRepository->createRequestStage([
                    'user_id' => $data['user_id'],
                    'request_status_id' => $new_status->id,
                    'rejected' => $rejected,
                    'updated_by' => $loggedUser->id,
                    'remark' => $data['remark']
                ]);
            }
            DB::commit();
            return ['message' => 'Request has been updated successfully.'];
        } catch(\Exception $exp) {
            DB::rollBack();
            throw new \Exception($exp->getMessage());
        }
    } 

    /**
     * Confirm request
     */
    public function confirmRequest(array $data)
    {
        try {
            $loggedUser = Auth::user();
            $user_id = $data['user_id'];
            $approved_status = $this->requestRepository->getStatusEnumBySlug('approved');
            $new_status = $this->requestRepository->getStatusEnumBySlug('confirmed');
            $active_request = $this->requestRepository->getRequestByIds($user_id, $approved_status->id);
            if($active_request){
                $membership = Membership::where('user_id', $user_id)->first();
                $member = Member::where('user_id', $user_id)->first();
    
                // Update current request status to checked
                $active_request->checked = 1;
                $active_request->save();
    
                $this->requestRepository->createRequestStage([
                    'user_id' => $user_id,
                    'request_status_id' => $new_status->id,
                    'checked' => 1,
                    'updated_by' => $loggedUser->id,
                    'remark' => $data['remark']
                ]);
    
                // Updating membership table
                $membership->mid = $data['mid'];
                $membership->start_date = $data['start_date'];
                $membership->updated_date = $data['start_date'];
                $membership->expiry_date = date('Y-m-d', strtotime('+1 year', strtotime($data['start_date'])));
                $membership->status = 'active';
                $membership->save();
    
                // Updating members table
                $member->active = 1;
                $member->save();
            }
        } catch(\Exception $exp) {
            DB::rollBack();
            throw new \Exception($exp->getMessage());
        }
    }
}
