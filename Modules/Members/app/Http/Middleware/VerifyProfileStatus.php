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
                        'success' => false,
                        'message' => 'Member details not added',
                        'is_member' => true,
                        'profile_completed' => false,
                    ];
                    return response()->json($response, 403);
                }
                return redirect('/member/detail');

            }else{

                // Checking membership request status
                $request_status = MembershipRequest::where('user_id', $user->id)->latest()->first();

                if ($request->is('api/*')) {
                    $response = [
                        'success' => false,
                        'message' => $request_status->request_status->description,
                        'is_member' => true,
                        'profile_completed' => true,
                        
                    ];
                    return response()->json($response, 403);
                }
                return redirect('/member/profile/pending');
            }
        }else{
            if ($request->is('api/*')) {
                $response = [
                    'success' => false,
                    'message' => 'Not a member',
                    'is_member' => false
                ];
                return response()->json($response, 401);
            }
            return redirect('home');
        }
        
    }
}
