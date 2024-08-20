<?php

/**
 * Membership request permission helper
 *
 * @return response()
 */

use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MembershipRequest;

// Checking permission for a single request
if (! function_exists('requestByPermission')) {
    function requestByPermission($request)
    {
        $user = Auth::user();
        $permitted = false;
        if($user->can('membership_request.verification.verify')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
            if($request->request_status_id == $status->id){
                $request['action'] = [
                    'slug' => 'verify',
                    'name' => 'Verify',
                    'title' => 'Verify request'
                ];
                $permitted = true;
            }
        }
        if($user->can('membership_request.review.review')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'verified')->first();
            if($request->request_status_id == $status->id){
                $request['action'] = [
                    'slug' => 'review',
                    'name' => 'Review',
                    'title' => 'Review request'
                ];
                $permitted = true;
            }
        }
        if($user->can('membership_request.approval.approve')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'reviewed')->first();
            if($request->request_status_id == $status->id){
                $request['action'] = [
                    'slug' => 'approve',
                    'name' => 'Approve',
                    'title' => 'Approve request'
                ];
                $permitted = true;
            }
        }
        if($user->can('membership_request.confirm')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'approved')->first();
            if($request->request_status_id == $status->id){
                $request['action'] = [
                    'slug' => 'confirm',
                    'name' => 'Confirm',
                    'title' => 'Confirm request'
                ];
                $permitted = true;
            }
        }
        if($user->can([
            'membership_request.verification.verify',
            'membership_request.review.review',
            'membership_request.approval.approve',
            'membership_request.confirm'
        ]) && $request->updated_by == Auth::user()->id){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'rejected')->first();
            if($request->request_status_id == $status->id){
                $request['action'] = [
                    'slug' => 'revise',
                    'name' => 'Revise',
                    'title' => 'Revise rejected request'
                ];
                $permitted = true;
            }
        }
        if($permitted){
            return $request;
        }else{
            return array();
        }
    }
}

// checking permission for multiple requests
if (! function_exists('requestsByPermission')) {
    function requestsByPermission($results)
    {
        $user = Auth::user();
        $requests = array();
        $permitted = false;
        foreach($results as $result){
            if($user->can('membership_request.verification.show')){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                if($result->request_status_id == $status->id){
                    $result['action'] = [
                        'slug' => 'verify',
                        'name' => 'Verify',
                        'title' => 'Verify request'
                    ];
                    $permitted = true;
                }
            }
            if($user->can('membership_request.review.show')){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'verified')->first();
                if($result->request_status_id == $status->id){
                    $result['action'] = [
                        'slug' => 'review',
                        'name' => 'review',
                        'title' => 'Review request'
                    ];
                    $permitted = true;
                }
            }
            if($user->can('membership_request.approval.show')){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'reviewed')->first();
                if($result->request_status_id == $status->id){
                    $result['action'] = [
                        'slug' => 'approve',
                        'name' => 'Approve',
                        'title' => 'Approve request'
                    ];
                    $permitted = true;
                }
            }
            if($user->can('membership_request.confirm')){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'approved')->first();
                if($result->request_status_id == $status->id){
                    $result['action'] = [
                        'slug' => 'confirm',
                        'name' => 'Confirm',
                        'title' => 'Confirm request'
                    ];
                    $permitted = true;
                }
            }
            $requests[] = $result;
        }
        if($permitted){
            return $requests;
        }else{
            return array();
        }
    }
}


if(! function_exists('requestStatusDisplay')){
    function requestStatusDisplay($user_id){

        $statuses = MemberEnum::where('type', 'request_status')->orderBy('order', 'ASC')->get();
        $actives =  MembershipRequest::where('user_id', $user_id)->orderBy('id', 'ASC')->get();
        foreach($actives as $active){
            if($active->rejected != null){
                for($i=0; $i < count($statuses); $i++){
                    if($statuses[$i]->id == $active->rejected){
                        //array_splice($statuses[$i], $i, 0 , $statuses[0]);
                        $statuses[$i] = $statuses[0];
                        //$i+1;
                    }
                }
            }
        }
        unset($statuses[0]);
        foreach($statuses as $key => $value){
            $statuses[$key]['checked'] = false;
        }
        foreach($statuses as $key => $value){
            foreach($actives as $active){
                if($value->id == $active->request_status_id  ||  $value->id == $active->request_status_id && $active->rejected ){ //add the request status history to the list
                    $statuses[$key]['checked'] = true;
                }
                if($value->id == $active->request_status_id){ // adding remarks to the list
                    $statuses[$key]['remark'] = $active->remark;
                }
            }
        }
        $return = array();
        foreach($statuses as $status){
            $return[] = $status;
            // if($status->slug == 'rejected'){
            //     break;
            // }
        }
        return $return;
        
    }
}
