<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class ApiMembershipController extends BaseController
{
    /**
     * Updating membership request status
     */
    public function changeStatus(Request $request)
    {
        
    }

    /**
     * Confirming membership
     */
    public function confirmMembership(Request $request)
    {

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
