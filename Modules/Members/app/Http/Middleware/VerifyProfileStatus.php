<?php

namespace Modules\Members\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MembershipRequest;

class VerifyProfileStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        
        $user = auth()->user();
        $member = Member::where('user_id', $user->id)->first();
        if($member){
            if($member->active){
                return $next($request);
            }
            // Checking member profile details.
            $details = MemberDetail::where('user_id', $user->id)->first();

            if(!$details || !$details->completed){
                if ($request->is('api/*')) {
                    //No details, returning error
                    $response = [
                        'success' => true,
                        'message' => 'Member details not added',
                        'is_member' => true,
                        'profile_completed' => false,
                        'active_membership' => false,
                    ];
                    return response()->json($response, 200);
                }
                return redirect('/member/detail');
            }else{
                // Checking membership request status
                $request_status = MembershipRequest::where('user_id', $user->id)->latest()->first();
                if ($request->is('api/*')) {
                    $response = [
                        'success' => true,
                        'message' => $request_status->request_status->description,
                        'is_member' => true,
                        'profile_completed' => true,   
                        'active_membership' => false, 
                    ];
                    return response()->json($response, 200);
                }
                return redirect('/member/profile/pending');
            }
        }else{
            if ($request->is('api/*')) {
                $response = [
                    'success' => true,
                    'message' => 'Not a member',
                    'is_member' => false
                ];
                return response()->json($response, 200);
            }
            return redirect('home');
        } 
    }
}
