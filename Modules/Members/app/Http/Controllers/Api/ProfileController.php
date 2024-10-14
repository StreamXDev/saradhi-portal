<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;

class ProfileController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedMember.user', 'relations.relatedMember.membership', 'relations.relatedMember.details', 'requests', 'committees', 'trustee'])->where('user_id' , $user->id)->first();
        $statuses = requestStatusDisplay($user->id);
        $current_status = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
        $pending_approval = $current_status && $current_status->request_status->slug === 'confirmed' ? false : true;

        //Member ID
        $idQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
        $member->membership->qrCode = 'data:image/png;base64, ' . base64_encode($idQr);
        $member->user->avatar = url('storage/images/'. $member->user->avatar);

        if($member->relations){
            foreach($member->relations as $key => $relative){
                $member->relations[$key]->relatedMember->user->avatar = url('storage/images/'. $member->relations[$key]->relatedMember->user->avatar);
                if($relative->relatedMember->active){
                    $spouseIdQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedMember->name,  'Membership ID' => $member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $member->relations[$key]->relatedMember->details->civil_id]));
                    $member->relations[$key]->relatedMember->membership->qrCode = 'data:image/png;base64, ' . base64_encode($spouseIdQr);
                }
            }
        }
        
        $data = [
            'member' => $member,
            'statuses' => $statuses,
            'is_member' => true,
            'profile_completed' => true,
            'active_membership' => $member->membership->status === 'active' ? true : false,
            'pending_approval' => $pending_approval,
            'current_status' => $current_status,
        ];
        return $this->sendResponse($data);
    }


    public function updateProfile(Request $request)
    {
        $logged_user = Auth::user();
        $logged_user_membership = Membership::where('user_id', $logged_user->id)->first();
        if(!$logged_user_membership){
            return $this->sendError('Not allowed', 'You are not a member', 405); 
        }

        $validator = Validator::make($request->all(), [
            'profile_type'    => 'required',
            'user_id'    => 'required',
            'avatar'    => 'required',
            'name'    => 'required|string',
            'calling_code'    => 'required',
            'phone'    => ['required', Rule::unique(User::class)],
            'whatsapp_code'    => 'required',
            'whatsapp'    => 'required|numeric',
            'emergency_phone_code'    => 'required',
            'emergency_phone'    => 'required|numeric',
            'blood_group'    => 'required|string',
            'dob'    => 'required|date_format:Y-m-d',
            'civil_id'    => 'required|string',
            'passport_no'    => 'required|string',
            'passport_expiry'    => 'required|date_format:Y-m-d',
            
        ],[
            'profile_type.required'    => 'Profile type is required',
            'user_id.required'    => 'Required field',
            'avatar.required'    => 'Photo is required',
            'name.required'    => 'Name is required field',
            'calling_code.required'    => 'Required field',
            'phone.required'    => 'Phone is required',
            'whatsapp_code.required'    => 'Required field',
            'whatsapp.required'    => 'Whatsapp is required',
            'emergency_phone_code.required'    => 'Required field',
            'emergency_phone.required'    => 'Emergency No. is required',
            'blood_group.required'    => 'Required field',
            'dob.required'    => 'Required field',
            'dob.date_format'    => 'Should be Y-m-d format',
            'civil_id.required'    => 'Required field',
            'passport_no.required'    => 'Required field',
            'passport_expiry.required'    => 'Required field',
            'passport_expiry.date_format'    => 'Should be Y-m-d format',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }

        $input = $request->all();
        $member = Member::where('user_id', $input['user_id']->id)->first();
        if($member && $member->active){
            $user = User::where('id', $input['user_id'])->first();
            $details = MemberDetail::where('user_id', $input['user_id']->id)->first();

            $existing_avatar = $user->avatar;
            $new_avatar = $input['avatar'];
            
            if($new_avatar !== $existing_avatar){
                if(Storage::exists('public/images/'.$existing_avatar)){
                    Storage::delete('public/images/'.$existing_avatar);
                }
                $avatarName = 'av'.$user->id.'_'.time().'.'.mime2ext($input['avatar_mime']);
                Storage::put('public/images/'.$avatarName, base64_decode($input['avatar']));
                $input['avatar'] = $avatarName;
            }

            $user->update(Arr::only($input, [
                'name','avatar','calling_code','phone'
            ]));
            $member->update(Arr::only($input, [
                'name', 'blood_group'
            ]));
            $details->update(Arr::only($input, [
                'whatsapp_code', 'whatsapp', 'emergency_phone_code', 'emergency_phone', 'dob', 'civil_id', 'paci', 'passport_no', 'passport_expiry', 'company', 'profession', 'company_address', 'sndp_branch', 'sndp_branch_number', 'sndp_unit'
            ]));

            $this->showProfile();
        }else{
            return $this->sendError('Not allowed', 'Requested member ('.$input['name'].') does not found. Please try again', 405); 
        }
    }
}
