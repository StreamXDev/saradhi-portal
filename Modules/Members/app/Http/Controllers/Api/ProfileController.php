<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDependent;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;

class ProfileController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        $data = $this->getProfileData();
        return $this->sendResponse($data, 'Profile Details');
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
            'whatsapp_code'    => 'required',
            'whatsapp'    => 'required|numeric',
            'emergency_phone_code'    => 'required',
            'emergency_phone'    => 'required|numeric',
            'blood_group'    => 'required|string',
            'dob'    => 'required|date_format:Y-m-d',
            'gender' => 'required|string',
            'civil_id'    => 'required|string',
            'passport_no'    => 'required|string',
            'passport_expiry'    => 'required|date_format:Y-m-d',
            
        ],[
            'profile_type.required'    => 'Profile type is required',
            'user_id.required'    => 'Required field',
            'avatar.required'    => 'Photo is required',
            'name.required'    => 'Name is required field',
            'calling_code.required'    => 'Required field',
            'whatsapp_code.required'    => 'Required field',
            'whatsapp.required'    => 'Whatsapp is required',
            'emergency_phone_code.required'    => 'Required field',
            'emergency_phone.required'    => 'Emergency No. is required',
            'blood_group.required'    => 'Required field',
            'dob.required'    => 'Required field',
            'dob.date_format'    => 'Should be Y-m-d format',
            'gender.required'    => 'Required field',
            'civil_id.required'    => 'Required field',
            'passport_no.required'    => 'Required field',
            'passport_expiry.required'    => 'Required field',
            'passport_expiry.date_format'    => 'Should be Y-m-d format',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }

        $input = $request->all();
        $member = Member::where('user_id', $input['user_id'])->first();
        if($member && $member->active){
            $user = User::where('id', $input['user_id'])->first();
            $details = MemberDetail::where('user_id', $input['user_id'])->first();

            $validator = Validator::make($request->all(), [
                'phone' => 'required|unique:users,phone,'.$user->phone
            ],[
                'phone.required'    => 'Phone is required',
                'phone.unique'    => 'Phone no. has already been taken',
            ]);

            $existing_avatar = $user->avatar;
            $new_avatar = $input['avatar'];
            
            if(isset($input['avatar_mime']) && $input['avatar_mime']){ // if avatar_mime, the avatar file is new
                if(Storage::exists('public/images/'.$existing_avatar)){
                    Storage::delete('public/images/'.$existing_avatar);
                }
                $avatarName = 'av'.$user->id.'_'.time().'.'.mime2ext($input['avatar_mime']);
                Storage::put('public/images/'.$avatarName, base64_decode($input['avatar']));
                $input['avatar'] = $avatarName;
            }else{
                $input['avatar'] = basename($input['avatar']);
            }

            $user->update(Arr::only($input, [
                'name','avatar','calling_code','phone'
            ]));
            $member->update(Arr::only($input, [
                'name', 'blood_group', 'gender'
            ]));
            $details->update(Arr::only($input, [
                'whatsapp_code', 'whatsapp', 'emergency_phone_code', 'emergency_phone', 'dob', 'civil_id', 'paci', 'passport_no', 'passport_expiry', 'company', 'profession', 'company_address', 'sndp_branch', 'sndp_branch_number', 'sndp_unit'
            ]));


            $data = $this->getProfileData();
            return $this->sendResponse($data, 'Profile Updated successfully');
        }else{
            return $this->sendError('Not allowed', 'Requested member ('.$input['name'].') does not found. Please try again', 405); 
        }
    }

    protected function getProfileData()
    {
        $user = Auth::user();
        $idQr = false;
        $profileCompleted = false;
        $pendingApproval = false;
        $activeMembership = false;
        $dormantMembership = false;
        $expiredMembership = false;
        $suspendedMembership = false;
        $currentStatus = null;
        $proofPending = false;
        $proofPendingTypes = [];
        
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedMember.user', 'relations.relatedMember.membership', 'relations.relatedMember.details', 'relations.relatedDependent', 'requests', 'committees', 'trustee'])->where('user_id' , $user->id)->first();
        
        // Checking id card proof is uploaded; Use case: a member logged in and the member is just registered and added profile details, but not uploaded proof
        if($member && $member->details){
            $profileCompleted =  true;
            if($member->membership->status === 'inactive'){
                if(!$member->details->photo_civil_id_front || !$member->details->photo_civil_id_back || !$member->details->photo_passport_front || !$member->details->photo_passport_back){
                    $proofPending = true;
                    $proofPendingTypes[] = 'self';
                }
            }
        }else{
            $proofPending = true; // in no details, usually is proof also pending
            $proofPendingTypes[] = 'self';
        }

        $statuses = requestStatusDisplay($user->id);
        $currentStatus = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
        if($currentStatus){
            $pendingApproval = $currentStatus->request_status->slug === 'confirmed' ? false : true;
        }
        //Member ID
        if($member->membership){
            $activeMembership = $member->membership->status === 'active' ? true : false;
            $dormantMembership = $member->membership->status === 'dormant' ? true : false;
            $expiredMembership = $member->membership->status === 'expired' ? true : false;
            $suspendedMembership = $member->membership->status === 'suspended' ? true : false;
            $idQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
            $member->membership->qrCode = 'data:image/png;base64, ' . base64_encode($idQr);
        }
        $member->user->avatar = url('storage/images/'. $member->user->avatar);

        
        if($member->relations){
            foreach($member->relations as $key => $relative){
                if($relative->related_member_id){
                    $member->relations[$key]->relatedMember->user->avatar = url('storage/images/'. $member->relations[$key]->relatedMember->user->avatar);

                    if($relative->relatedMember && $relative->relatedMember->details){
                        if($relative->relatedMember->membership->status === 'inactive'){
                            if(!$relative->relatedMember->details->photo_civil_id_front || $relative->relatedMember->details->photo_civil_id_back || $relative->relatedMember->details->photo_passport_front || $relative->relatedMember->details->photo_passport_back){
                                $proofPending = true;
                                $proofPendingTypes[] = 'spouse';
                            }
                        }
                    }else{
                        $proofPending = true; // in no details, usually is proof also pending
                        $proofPendingTypes[] = 'spouse';
                    }

                    if($relative->relatedMember->active){
                        $spouseIdQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedMember->name,  'Membership ID' => $member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $member->relations[$key]->relatedMember->details->civil_id]));
                        $member->relations[$key]->relatedMember->membership->qrCode = 'data:image/png;base64, ' . base64_encode($spouseIdQr);
                    }
                }else if($relative->related_dependent_id){
                    $member->relations[$key]->relatedDependent->avatar = url('storage/images/'. $member->relations[$key]->relatedDependent->avatar);
                    $childIdQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedDependent->name,  'Membership ID' => $member->relations[$key]->relatedDependent->parent_mid, 'Civil ID' => $member->relations[$key]->relatedDependent->civil_id]));
                    $member->relations[$key]->relatedDependent->qrCode = 'data:image/png;base64, ' . base64_encode($childIdQr);
                }
                
            }
        }

        $app_action = false;
        if($profileCompleted){
            if(!$proofPending){
                if($activeMembership){
                    $app_action = 'member_card';
                }else{
                    if($dormantMembership){
                        $app_action = 'dormant_membership';
                    }else if($expiredMembership){
                        $app_action = 'expired_membership';
                    }else if($suspendedMembership){
                        $app_action = 'suspended_membership';
                    }else{
                        if($pendingApproval){
                            $app_action = 'pending_approval';
                        }else{
                            $app_action = 'inactive_membership';
                        }
                    }
                }
            }else{
                $app_action = 'add_proof';
            }
        }else{
            $app_action = 'complete_profile';
        }


        $data = [
            'is_member' => $member ? true : false,
            'profile_completed' => $profileCompleted,
            'active_membership' => $activeMembership,
            'dormant_membership' => $dormantMembership,
            'expired_membership' => $expiredMembership,
            'suspended_membership' => $suspendedMembership,
            'pending_approval' => $pendingApproval,
            'current_status' => $currentStatus,
            'proof_pending' => $proofPending,
            'proof_pending_types' => $proofPendingTypes,
            'family_request' => $profileCompleted ? ($member->membership->type === 'family' ? true : false) : null,
            'user' => $user,
            'member' => $member,
            'statuses' => $statuses,
            'unit_change_enabled' => false, // If the unit change feature is enabled, the action will be shown on app
            'unit_change_request' => false, //should be added the unit change request status
            'app_action' => $app_action
        ];

        return $data;
    }

    /* ------------------------------- DEPENDENT ----------------------------------------------------- */

    public function createDependent()
    {
        $countries = Country::with('regions')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = array(
            ['id' => 1, 'name'=>'Male', 'slug' => 'male'], 
            ['id' => 2, 'name' => 'Female', 'slug' => 'female']
        );
        $data = [
            'countries' => $countries,
            'blood_groups' => $blood_groups,
            'gender' => $gender
        ];
        return $this->sendResponse($data);
    }

    // storing dependent data
    public function storeDependent(Request $request)
    {
        $user = Auth::user();

        $requesting_member = Member::with([
            'user', 
            'details', 
            'membership', 
            'localAddress', 
            'permanentAddress', 
            'relations', 
            'relations.relatedMember.user', 
            'relations.relatedMember.membership', 
            'relations.relatedMember.details', 
            'relations.relatedDependent', 
            'requests', 
            'committees', 
            'trustee'
        ])->where('user_id' , $user->id)->first();
        $validator = Validator::make($request->all(), ...$this->dependentValidationRules($request));
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), 403);       
        }
        $input = $request->all();
        
        if($input['type'] === 'spouse'){
            $userInput = [
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make(Str::random(10)),
            ];
            $dependent_user = User::create($userInput);
            $dependent_user->assignRole(['Member']);
            $dependent ['user_id'] = $dependent_user->id;
            $dependent ['name'] = $dependent_user->name;
            $dependent_member = Member::create($dependent);
            
            $dependent_avatarName = 'av'.$dependent_user->id.'_'.time().'.'.mime2ext($input['avatar_mime']);
            Storage::put('public/images/'.$dependent_avatarName, base64_decode($input['avatar']));
            DB::beginTransaction();
            MemberDetail::updateOrCreate(
                ['user_id' => $dependent_user->id],
                [
                    'member_unit_id' => $requesting_member->details->member_unit->id,
                    'civil_id' => $input['civil_id'],
                    'dob' => $input['dob'],
                    'whatsapp' => $input['whatsapp'],
                    'whatsapp_code' => $input['whatsapp_code'],
                    'emergency_phone' => $input['emergency_phone'],
                    'emergency_phone_code' => $input['emergency_phone_code'],
                    'passport_no' => $input['passport_no'],
                    'passport_expiry' => $input['passport_expiry'],
                    'paci' => isset($input['paci']) ? $input['paci'] : null,
                    'sndp_branch' => isset($input['sndp_branch']) ? $input['sndp_branch'] : null,
                    'sndp_branch_number' => isset($input['sndp_branch_number']) ? $input['sndp_branch_number'] : null,
                    'sndp_union' => isset($input['sndp_union']) ? $input['sndp_union'] : null,
                    'completed' => 0
                ]
            );
            Member::where('user_id', $dependent_user->id)->update([
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group'],
                'type' => 'spouse'
            ]);
            
            User::where('id', $dependent_user->id)->update([
                'phone' => $input['phone'],
                'calling_code' => $input['calling_code'],
                'avatar' => $dependent_avatarName,
            ]);
            Membership::create([
                'user_id' => $dependent_user->id,
                'type' => $input['type'],
                'family_in' => isset($input['family_in']) ? $input['family_in'] : ($input['type'] == 'family' ? 'kuwait' : 'india'),
                'introducer_name' => $user->name,
                'introducer_phone' => $user->calling_code.$user->phone,
                'introducer_mid' => $requesting_member->membership->mid,
                'introducer_unit' => $requesting_member->details->member_unit->id,
            ]);
            // Create contacts table entry
            MemberLocalAddress::create([
                'user_id' => $dependent_user->id,
                'governorate' => $requesting_member->localAddress->governorate,
                'line_1' => $requesting_member->localAddress->line_1,
                'building' => $requesting_member->localAddress->building,
                'flat' => $requesting_member->localAddress->flat,
                'floor' => $requesting_member->localAddress->floor,
                'country' => $requesting_member->localAddress->country,
                'region' => $requesting_member->localAddress->region,
                'city' => $requesting_member->localAddress->city,
                'zip' => $requesting_member->localAddress->zip,
            ]);
            MemberPermanentAddress::create([
                'user_id' => $dependent_user->id,
                'line_1' => $requesting_member->permanentAddress->line_1,
                'line_2' => $requesting_member->permanentAddress->line_2,
                'country' => $requesting_member->permanentAddress->country,
                'region' => $requesting_member->permanentAddress->region,
                'district' => $requesting_member->permanentAddress->district,
                'city' => $requesting_member->permanentAddress->city,
                'zip' => $requesting_member->permanentAddress->zip,
                'contact' => $requesting_member->permanentAddress->contact,
            ]);
            $relation = MemberEnum::where('type', 'relationship')->where('slug', 'spouse')->first();
            MemberRelation::create([
                'member_id' => $requesting_member->id,
                'related_member_id' => $dependent_member->id,
                'relationship_id' => $relation->id,
            ]);
            MemberRelation::create([
                'member_id' => $dependent_member->id,
                'related_member_id' => $requesting_member->id,
                'relationship_id' => $relation->id,
            ]);
            DB::commit();
        }else if($input['type'] === 'child'){
            $childInput = [
                'name' => $input['name'],
                'email' => isset($input['email']) ? $input['email'] : null,
                'calling_code' => isset($input['calling_code']) ? $input['calling_code'] : null,
                'phone' => isset($input['phone']) ? $input['phone'] : null,
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group'],
                'civil_id' => $input['civil_id'],
                'dob' => $input['dob'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'parent_user_id' => $user->id,
                'parent_mid' => $requesting_member->membership->mid,
                'type' => 'child'
            ];
            DB::beginTransaction();
            $child = MemberDependent::create($childInput);
            if(isset($input['avatar'])){
                $child_avatar = 'av'.$child->id.'_'.time().'.'.mime2ext($input['avatar_mime']);
                Storage::put('public/images/'.$child_avatar, base64_decode($input['avatar']));
                MemberDependent::where('id', $child->id)->update([
                    'avatar' => $child_avatar,
                ]);
            }

            $relations_against_primary_member = MemberRelation::where('member_id', $requesting_member->id)->get();
            $parent_primary = $requesting_member->id;
            $parent_spouse = null;
            $siblings = [];
            foreach($relations_against_primary_member as $primary_relations){
                if($primary_relations->related_member_id !== null){
                    $rm = Member::where('id',$primary_relations->related_member_id)->first();
                    if($rm->type === 'primary'){
                        $parent_primary = $rm->id;
                    }else{
                        $parent_spouse = $rm->id;
                    }
                }else if($primary_relations->related_dependent_id !== null){
                    $rd = MemberDependent::where('id', $primary_relations->related_dependent_id)->first();
                    $siblings[] = $rd->id;
                }
            }
            $parent_relation_type = MemberEnum::where('type', 'relationship')->where('slug', 'parent')->first();
            $child_relation_type = MemberEnum::where('type', 'relationship')->where('slug', 'child')->first();
            $sibling_relation_type = MemberEnum::where('type', 'relationship')->where('slug', 'sibling')->first();
            if($parent_primary){
                MemberRelation::create([
                    'member_id' => $parent_primary,
                    'related_dependent_id' => $child->id,
                    'relationship_id' => $parent_relation_type->id,
                ]);
                MemberRelation::create([
                    'related_member_id' => $parent_primary,
                    'dependent_id' => $child->id,
                    'relationship_id' => $child_relation_type->id,
                ]);
            }
            if($parent_spouse){
                MemberRelation::create([
                    'member_id' => $parent_spouse,
                    'related_dependent_id' => $child->id,
                    'relationship_id' => $parent_relation_type->id,
                ]);
                MemberRelation::create([
                    'related_member_id' => $parent_spouse,
                    'dependent_id' => $child->id,
                    'relationship_id' => $child_relation_type->id,
                ]);
            }
            if($siblings){
                foreach($siblings as $sibling){
                    MemberRelation::create([
                        'dependent_id' => $sibling,
                        'related_dependent_id' => $child->id,
                        'relationship_id' => $sibling_relation_type->id,
                    ]);
                    MemberRelation::create([
                        'related_dependent_id' => $sibling,
                        'dependent_id' => $child->id,
                        'relationship_id' => $sibling_relation_type->id,
                    ]);
                }
            }

            DB::commit();
        }
        
        $data = $this->getProfileData();
        return $this->sendResponse($data, 'Profile Details');
    }

    public function updateDependent(Request $request)
    {
        $logged_user = Auth::user();
        $logged_user_membership = Membership::where('user_id', $logged_user->id)->first();
        if(!$logged_user_membership){
            return $this->sendError('Not allowed', 'You are not a member', 405); 
        }

        $validator = Validator::make($request->all(), [
            'profile_type'    => 'required',
            'id'    => 'required',
            'avatar'    => 'required',
            'name'    => 'required|string',
            'blood_group'    => 'required|string',
            'dob'    => 'required|date_format:Y-m-d',
            'gender' => 'required|string',
            'civil_id'    => 'required|string',
            'passport_no'    => 'required|string',
            'passport_expiry'    => 'required|date_format:Y-m-d',
            
        ],[
            'profile_type.required'    => 'Profile type is required',
            'id.required'    => 'Required field',
            'avatar.required'    => 'Photo is required',
            'name.required'    => 'Name is required field',
            'blood_group.required'    => 'Required field',
            'dob.required'    => 'Required field',
            'dob.date_format'    => 'Should be Y-m-d format',
            'gender.required'    => 'Required field',
            'civil_id.required'    => 'Required field',
            'passport_no.required'    => 'Required field',
            'passport_expiry.required'    => 'Required field',
            'passport_expiry.date_format'    => 'Should be Y-m-d format',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }

        $input = $request->all();
        $child = MemberDependent::where('id', $input['id'])->first();

        if($child){
            $existing_avatar = $child->avatar;
            $new_avatar = $input['avatar'];

            if(isset($input['avatar_mime']) && $input['avatar_mime']){ // if avatar_mime, the avatar file is new
                if(Storage::exists('public/images/'.$existing_avatar)){
                    Storage::delete('public/images/'.$existing_avatar);
                }
                $avatarName = 'av'.$child->id.'_'.time().'.'.mime2ext($input['avatar_mime']);
                Storage::put('public/images/'.$avatarName, base64_decode($input['avatar']));
                $input['avatar'] = $avatarName;
            }else{
                $input['avatar'] = basename($input['avatar']);
            }

            $child->update(Arr::only($input, [
                'avatar', 'name', 'email', 'blood_group', 'dob', 'gender', 'civil_id', 'passport_no', 'passport_expiry'
            ]));
    
            $data = $this->getProfileData();
            return $this->sendResponse($data, 'Profile Updated successfully');
        }else{
            return $this->sendError('Not allowed', 'Requested child ('.$input['name'].') does not found. Please try again', 405); 
        }
    }

    public function deleteDependent(Request $request)
    {
        $input = $request->all();
        $child = MemberDependent::where('id', $input['id'])->first();
        $relation1 = MemberRelation::where('dependent_id', $input['id'])->get();
        $relation2 = MemberRelation::where('related_dependent_id', $input['id'])->get();
        foreach($relation1 as $rel){
            $rel->delete();
        }
        foreach($relation2 as $rel){
            $rel->delete();
        }
        $child->delete();
        $data = $this->getProfileData();
        return $this->sendResponse($data, 'Child deleted successfully');
    }

    protected function dependentValidationRules($request)
    {
        
        if($request->type === 'child'){
            $rules['name'] = ['required', 'string'];
            $rules['gender'] = ['required', 'string'];
            $rules['dob'] = ['required', 'date_format:Y-m-d'];
            $rules['blood_group'] = ['required', 'string'];
            $rules['civil_id'] = ['required', 'string'];
            $rules['passport_no'] = ['required', 'string'];
            $rules['passport_expiry'] = ['required', 'date_format:Y-m-d'];

            $messages['name.required'] = 'Name is required';
            $messages['gender.required'] = 'Gender is required';
            $messages['dob.required'] = 'DOB is required';
            $messages['dob.date_format'] = 'Should be Y-m-d format';
            $messages['blood_group.required'] = 'Blood group is required';
            $messages['civil_id.required'] = 'Civil ID is required';
            $messages['civil_id.string'] = 'Invalid Civil ID';
            $messages['passport_no.required'] = 'Passport no. is required';
            $messages['passport_expiry.required'] = 'Expiry date is required';
            $messages['passport_expiry.date_format'] = 'Should be Y-m-d format';
        }else if($request->type === 'spouse'){
            $rules['name'] = ['required', 'string'];
            $rules['email'] = ['required', Rule::unique(User::class, 'email')];
            $rules['phone'] = ['required', Rule::unique(User::class, 'phone')];
            $rules['calling_code'] = ['required'];
            $rules['whatsapp'] = ['required', 'numeric'];
            $rules['whatsapp_code'] = ['required'];
            $rules['emergency_phone'] = ['required', 'numeric'];
            $rules['emergency_phone_code'] = ['required'];
            $rules['gender'] = ['required', 'string'];
            $rules['dob'] = ['required', 'date_format:Y-m-d'];
            $rules['blood_group'] = ['required', 'string'];
            $rules['civil_id'] = ['required', 'string'];
            $rules['passport_no'] = ['required', 'string'];
            $rules['passport_expiry'] = ['required', 'date_format:Y-m-d'];

            $messages['name.required'] = 'Name is required';
            $messages['email.required'] = 'Email is required';
            $messages['email.unique'] = 'Email already registered';
            $messages['phone.required'] = 'Phone is required';
            $messages['phone.unique'] = 'Number already used';
            $messages['calling_code.required'] = 'Required';
            $messages['whatsapp.required'] = 'Whatsapp is required';
            $messages['whatsapp.numeric'] = 'Whatsapp number should be a number';
            $messages['whatsapp_code.required'] = 'Required';
            $messages['emergency_phone.required'] = 'Emergency Phone required';
            $messages['emergency_phone.numeric'] = 'Should be a number';
            $messages['emergency_phone_code.required'] = 'Required';
            $messages['gender.required'] = 'Gender is required';
            $messages['dob.required'] = 'DOB is required';
            $messages['dob.date_format'] = 'Should be Y-m-d format';
            $messages['blood_group.required'] = 'Blood group is required';
            $messages['civil_id.required'] = 'Civil ID is required';
            $messages['civil_id.string'] = 'Invalid Civil ID';
            $messages['passport_no.required'] = 'Passport no. is required';
            $messages['passport_expiry.required'] = 'Expiry date is required';
            $messages['passport_expiry.date_format'] = 'Should be Y-m-d format';
        }

        return [
            $rules,
            $messages
        ];
    }
}
