<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MembershipRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:membership_request.verification.show', ['only' => ['showPendingVerification']]);
        $this->middleware('permission:membership_request.verification.verify', ['only' => ['verify']]);
        $this->middleware('permission:membership_request.review.show', ['only' => ['showPendingReview']]);
        $this->middleware('permission:membership_request.review.review', ['only' => ['review']]);
        $this->middleware('permission:membership_request.approval.show', ['only' => ['showPendingApproval']]);
        $this->middleware('permission:membership_request.approval.approve', ['only' => ['approve']]);
        $this->middleware('permission:membership_request.confirm', ['only' => ['confirm']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function showPendingVerification()
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function verify()
    {
        
    }
    
    /**
     * Display a listing of the resource.
     */
    public function showPendingReview()
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function review()
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function showPendingApproval()
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function approve()
    {
        
    }

    /**
     * Display a listing of the resource.
     */
    public function confirm()
    {
        // membership table : add mid, start_date, updated_date, expiry_date, type, etc.
        // make member active
        // send notification to member
    }


}
