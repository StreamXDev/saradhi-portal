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
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Notifications\MembershipApproval;
use Modules\Members\Services\MemberRequestService;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(
        protected MemberRequestService $requestService
    )
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
        $menuParent = 'requests';
        $results = MembershipRequest::with(['member', 'details', 'user', 'member.relations.relationship'])->where('checked', 0)->orderBy('id', 'desc');
        
        if($request->query('type')){
            $type = $request->query('type');
        }else{
            $type = 'submitted';
        }
        switch ($type) {
            case 'submitted':
                $results = $results->where('request_status_id', 3)->paginate();
                break;
            case 'verified':
                $results = $results->where('request_status_id', 4)->paginate();
                break;
            case 'reviewed':
                $results = $results->where('request_status_id', 5)->paginate();
                break;
            case 'approved':
                $results = $results->where('request_status_id', 6)->paginate();
                break;
            case 'rejected':
                $results = $results->where('request_status_id', 1)->paginate();
                break;
            default:
                $results = $results->where('request_status_id', 3)->paginate();
                $type = 'submitted';
                break; 
        }
        //dd($results);
        foreach($results as $requested_user){
            $requested_user->duplicate_civil_id = null;
            $requested_civil_id = $requested_user->details->civil_id;
            $duplicate = MemberDetail::select('user_id')->where('civil_id',$requested_civil_id)->where('user_id', '!=', $requested_user->user_id)->get();
            if($duplicate){
                $requested_user->duplicate_civil_id = $duplicate->count();
            }
        };
        $requests = requestsByPermission($results);
        //dd($requests);
        return view('members::admin.membership.request', compact('requests','type', 'menuParent'));
    }

    public function changeStatus(Request $request)
    {
        $input = $request->all();
        $this->requestService->changeStatus($input);
        return redirect()->back();
    }

    public function confirmMembershipRequest(Request $request){
        
        $input = $request->all();
        $this->requestService->confirmRequest($input);

        $user_id = $input['user_id'];
        $user = User::find($user_id);

        $messages['hi'] = "Hi {$user->name}";
        $messages['message'] = "Congratulations!. Your membership application has been approved.";
        $user->notify(new MembershipApproval($messages));

        return redirect()->back();
    }

    protected function validationRules()
    {
        $rules =  [
            'user_id'      => ['required', Rule::exists(User::class, 'id')],
            //'mid'          => ['required', Rule::unique(Membership::class, 'mid')]
            'mid'          => ['required']
        ];
        $messages = [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User ID is not valid',
            'mid.required' => 'Membership ID is required',
            //'mid.exists' => 'Membership ID is already used',
        ];

        return [
            $rules,
            $messages
        ];
    }
 
}
