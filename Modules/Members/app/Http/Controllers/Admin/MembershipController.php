<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MembershipRequest;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware(
            'permission:membership_request.verification.show|membership_request.review.show|membership_request.approval.show', 
            ['only' => ['requests']]
        );
    }
    
    /**
     * Display a listing of the resource which is pending for treasurer verification.
     */
    public function requests()
    {
        $user = Auth::user();

        $requests = MembershipRequest::with(['member', 'details', 'user'])->where('checked',0)->get();

        if($user->can('membership_request.verification.show')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'pending')->first();
        }
        else if($user->can('membership_request.review.show')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'verified')->first();
        }
        else if($user->can('membership_request.approval.show')){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'reviewed')->first();
        }

        //dd($requests);
        return view('members::admin.membership.request', compact('requests'));
    }

    public function changeStatus(Request $request)
    {
        
        $input = $request->all();
        $user_id = $input['user_id'];
        $current_status_id = $input['current_status_id'];
        $active_request = MembershipRequest::where('user_id', $user_id)->where('request_status_id', $current_status_id)->where('checked', 0)->first();

        if($active_request){
            // get current status id order number;
            // get next status id (current+1)
            // mark current status checked
            // add next status with updated by current user
        }
        
        //check the user exists, has request, previous request is checked, no status entry greater than previous request
        //$request_exists = MembershipRequest::where('user_id', $user_id)->where()

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('members::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('members::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('members::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
