<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Notifications\MembershipApproval;

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
            'permission:membership_request.verification.show|membership_request.review.show|membership_request.approval.show|membership_request.confirm', 
            ['only' => ['requests']]
        );
        $this->middleware(
            'permission:membership_request.verification.verify|membership_request.review.review|membership_request.approval.approve|membership_request.confirm', 
            ['only' => ['changeStatus']]
        );
        $this->middleware('permission:membership_request.confirm', ['only' => ['confirmMembershipRequest']]);
    }
    
    /**
     * Display list of membership requests.
     */
    public function requests(Request $request)
    {
        $results = MembershipRequest::with(['member', 'details', 'user', 'member.relations.relationship'])->where('checked', 0)->get()->sortByDesc('id');
        
        if($request->query('type')){
            $type = $request->query('type');
            switch ($type) {
                case 'submitted':
                    $results = $results->where('request_status_id', 3);
                    break;
                case 'verified':
                    $results = $results->where('request_status_id', 4);
                    break;
                case 'reviewed':
                    $results = $results->where('request_status_id', 5);
                    break;
                case 'approved':
                    $results = $results->where('request_status_id', 6);
                    break;
                default:
                    $results = $results->where('request_status_id', 3);
                    $type = 'submitted';
                    break; 
            }
        }else{
            $type = 'submitted';
        }
        $requests = requestsByPermission($results);
        return view('members::admin.membership.request', compact('requests','type'));
    }

    public function changeStatus(Request $request)
    {
        $user = Auth::user();

        $input = $request->all();
        $user_id = $input['user_id'];
        $current_status_id = $input['current_status_id'];
        $active_request = MembershipRequest::where('user_id', $user_id)->where('request_status_id', $current_status_id)->where('checked', 0)->first();

        if($active_request){

            if($request->input('action') == 'reject' && $active_request->slug == 'rejected'){
                return redirect()->back()->with('error', 'The request already rejected');
            }

            $current_status = MemberEnum::where('type', 'request_status')->where('id', $current_status_id)->first();
            $current_status_order = $current_status->order;
            $next_status_order = $current_status_order + 1;
            
            if($current_status->order == 0){ //if current status is rejected
                $rejected_status = MembershipRequest::where('user_id', $user_id)->where('request_status_id', $current_status_id)->first();
                $next_status_order = $rejected_status->rejected;
            }
            
            $active_request->checked = 1;
            $active_request->save();

            $rejected = null;
            if($request->input('action') == 'reject'){
                $new_status = MemberEnum::where('type', 'request_status')->where('order', 0)->first();
                $rejected = $current_status_id;
            }else{
                $new_status = MemberEnum::where('type', 'request_status')->where('order', $next_status_order)->first();
            }
            MembershipRequest::create([
                'user_id' => $user_id,
                'request_status_id' => $new_status->id,
                'rejected' => $rejected,
                'updated_by' => $user->id,
                'remark' => $input['remark']
            ]);
        }

        return redirect()->back();
    }

    public function confirmMembershipRequest(Request $request){
        $admin = Auth::user();
        $input = $request->all();

        $validator = Validator::make($request->all(), ...$this->validationRules());
        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }

        $user_id = $input['user_id'];

        // check active request and it is ready to confirm
        $approved_status = MemberEnum::where('type', 'request_status')->where('slug', 'approved')->first();
        $new_status = MemberEnum::where('type', 'request_status')->where('slug', 'confirmed')->first();
        $active_request = MembershipRequest::where('user_id', $user_id)->where('request_status_id', $approved_status->id)->where('checked', 0)->first();
        
        if($active_request){
            $membership = Membership::where('user_id', $user_id)->first();
            $member = Member::where('user_id', $user_id)->first();
            $user = User::find($user_id);

            // Update current request status to checked
            $active_request->checked = 1;
            $active_request->save();

            MembershipRequest::create([
                'user_id' => $user_id,
                'request_status_id' => $new_status->id,
                'checked' => 1,
                'updated_by' => $admin->id,
                'remark' => $input['remark']
            ]);

            // Updating membership table
            $membership->mid = $input['mid'];
            $membership->start_date = now();
            $membership->updated_date = now();
            $membership->expiry_date = date('Y-m-d', strtotime('+1 year'));
            $membership->status = 'active';
            $membership->save();

            // Updating members table
            $member->active = 1;
            $member->save();

            $messages['hi'] = "Hi {$user->name}";
            $messages['message'] = "Congratulations!. Your membership application has been approved.";
            $user->notify(new MembershipApproval($messages));

            return redirect()->back()->with('success', 'Successfully confirmed the request');
        }

        return Redirect::back()->withErrors(['request' => ['Invalid request']]);

    }

    protected function validationRules()
    {
        $rules =  [
            'user_id'      => ['required', Rule::exists(User::class, 'id')],
            'mid'          => ['required', 'string']
        ];

        $messages = [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User ID is not valid',
            'mid.required' => 'Membership ID is required',
        ];

        return [
            $rules,
            $messages
        ];
    }

 
}
