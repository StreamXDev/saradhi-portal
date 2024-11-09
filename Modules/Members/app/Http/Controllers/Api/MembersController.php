<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Models\MemberUnit;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class MembersController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:profile.view', ['only' => ['showProfile, createDetails']]);
    }

    /**
     * Send the form for creating a new resource.
     */
    public function createDetails()
    {
        $countries = [];
        //$countries = Country::with('regions')->where('active', 1)->get();
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = array(
            ['id' => 1, 'name'=>'Male', 'slug' => 'male'], 
            ['id' => 2, 'name' => 'Female', 'slug' => 'female']
        );
        $district_kerala = array(
            ['id' => 1, 'name' => 'Alappuzha', 'slug' => 'alappuzha'],
            ['id' => 2, 'name' => 'Ernakulam', 'slug' => 'ernakulam'],
            ['id' => 3, 'name' => 'Idukki', 'slug' => 'idukki'],
            ['id' => 4, 'name' => 'Kannur', 'slug' => 'kannur'],
            ['id' => 5, 'name' => 'Kasaragod', 'slug' => 'kasaragod'],
            ['id' => 6, 'name' => 'Kollam', 'slug' => 'kollam'],
            ['id' => 7, 'name' => 'Kottayam', 'slug' => 'kottayam'],
            ['id' => 8, 'name' => 'Kozhikkode', 'slug' => 'kozhikkode'],
            ['id' => 9, 'name' => 'Malappuram', 'slug' => 'malappuram'],
            ['id' => 10, 'name' => 'Palakkad', 'slug' => 'palakkad'],
            ['id' => 11, 'name' => 'Pathanamthitta', 'slug' => 'pathanamthitta'],
            ['id' => 12, 'name' => 'Thiruvananthapuram', 'slug' => 'thriuvananthapuram'],
            ['id' => 13, 'name' => 'Thrissur', 'slug' => 'thrissur'],
            ['id' => 14, 'name' => 'Wayanad', 'slug' => 'wayanad'],
            ['id' => 15, 'name' => 'Other', 'slug' => 'other'],
        );
        $data = [
            'countries' => $countries,
            'units' => $units,
            'blood_groups' => $blood_groups,
            'gender' => $gender,
            'districts' => $district_kerala
        ];
        return $this->sendResponse($data);
    }

    /**
     * Send the form for creating a new resource.
     */
    public function storeDetails(Request $request)
    {
        $user = Auth::user();
        $existing_membership_data = Membership::where('user_id', $user->id)->first();
        $existing_membership_request = MembershipRequest::where('user_id', $user->id)->latest()->first();
        if($existing_membership_data){
            return $this->sendError('Not allowed.', 'You already a member', 405); 
        }
        if($existing_membership_request){
            return $this->sendError('Already requested.', 'Your membership '.strtolower($existing_membership_request->request_status->description), 405); 
        }
        $validator = Validator::make($request->all(), ...$this->validationRules($request));
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), 403);       
        }
        $input = $request->all();
        $avatarName = 'av'.$user->id.'_'.time().'.'.mime2ext($input['photo_mime']);
        Storage::put('public/images/'.$avatarName, base64_decode($input['photo']));
        $input['avatar'] = $avatarName;
        DB::beginTransaction();
        try {
            // Adding member details
            MemberDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'member_unit_id' => $input['member_unit_id'],
                    'civil_id' => $input['civil_id'],
                    'dob' => $input['dob'],
                    'whatsapp' => $input['whatsapp'],
                    'whatsapp_code' => $input['whatsapp_code'],
                    'emergency_phone' => $input['emergency_phone'],
                    'emergency_phone_code' => $input['emergency_phone_code'],
                    'company' => isset($input['company']) ? $input['company'] : null,
                    'profession' => isset($input['profession']) ? $input['profession'] : null,
                    'company_address' => isset($input['company_address']) ? $input['company_address'] : null,
                    'passport_no' => $input['passport_no'],
                    'passport_expiry' => $input['passport_expiry'],
                    'paci' => isset($input['paci']) ? $input['paci'] : null,
                    'sndp_branch' => isset($input['sndp_branch']) ? $input['sndp_branch'] : null,
                    'sndp_branch_number' => isset($input['sndp_branch_number']) ? $input['sndp_branch_number'] : null,
                    'sndp_union' => isset($input['sndp_union']) ? $input['sndp_union'] : null,
                    'completed' => 0
                ]
            );

            // Updating members table (It has been created an entry when the time of registration)
            Member::where('user_id', $user->id)->update([
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group']
            ]);
            //Updating users table - phone number and avatar
            User::where('id', $user->id)->update([
                'phone' => $input['phone'],
                'calling_code' => $input['calling_code'],
                'avatar' => $avatarName,
            ]);

            // Create membership table entry
            $introducer_country_code = isset($input['introducer_country_code']) ? $input['introducer_country_code'] : $input['calling_code'];
            $introducer_phone = isset($input['introducer_phone']) ? $input['introducer_phone'] : '';
            $permanent_cCod =  isset($input['permanent_address_country_code']) ?  $input['permanent_address_country_code'] : '91';
            $permanent_phone = isset($input['permanent_address_contact']) ? $input['permanent_address_contact'] : '' ;
            Membership::create([
                'user_id' => $user->id,
                'type' => $input['type'],
                'introducer_name' => isset($input['introducer_name']) ? $input['introducer_name'] : null,
                'introducer_phone' => $introducer_country_code.$introducer_phone,
                'introducer_mid' => isset($input['introducer_mid']) ? $input['introducer_mid'] : null,
                'introducer_unit' => isset($input['introducer_unit']) ? $input['introducer_unit'] : null,
            ]);
            
            // Create contacts table entry
            MemberLocalAddress::create([
                'user_id' => $user->id,
                'governorate' => $input['governorate'],
                'line_1' => $input['local_address_area'],
                'building' => isset($input['local_address_building']) ? $input['local_address_building'] : null,
                'flat' => isset($input['local_address_flat']) ? $input['local_address_flat'] : null,
                'floor' => isset($input['local_address_floor']) ? $input['local_address_floor'] : null,
            ]);
            
            // Adding introducers details
            MemberPermanentAddress::create([
                'user_id' => $user->id,
                'line_1' => $input['permanent_address_line_1'],
                'line_2' => isset($input['permanent_address_line_2']) ? $input['permanent_address_line_2'] : null,
                'district' => isset($input['permanent_address_district']) ? $input['permanent_address_district'] : null,
                'contact' => $permanent_cCod.$permanent_phone,
            ]);
            
            // adding spouse
            // Adding spouse if membership type is family
            if($input['type'] === 'family'){
                $userInput['name'] = $input['spouse_name'];
                $userInput['email'] = $input['spouse_email'];
                $userInput['password'] = Hash::make(Str::random(10));
                $spouse_user = User::create($userInput);
                $spouse_user->assignRole(['Member']);
                $spouse ['user_id'] = $spouse_user->id;
                $spouse ['name'] = $spouse_user->name;
                $spouse_member = Member::create($spouse);
                // Sending OTP
                //Storing attachments
                $spouse_avatarName = 'av'.$spouse_user->id.'_'.time().'.'.mime2ext($input['spouse_photo_mime']);
                Storage::put('public/images/'.$spouse_avatarName, base64_decode($input['spouse_photo']));
                // Spouse Member details
                MemberDetail::updateOrCreate(
                    ['user_id' => $spouse_user->id],
                    [
                        'member_unit_id' => $input['member_unit_id'],
                        'civil_id' => $input['spouse_civil_id'],
                        'dob' => $input['spouse_dob'],
                        'whatsapp' => $input['spouse_whatsapp'],
                        'whatsapp_code' => $input['spouse_whatsapp_code'],
                        'emergency_phone' => $input['spouse_emergency_phone'],
                        'emergency_phone_code' => $input['spouse_emergency_phone_code'],
                        'passport_no' => $input['spouse_passport_no'],
                        'passport_expiry' => $input['spouse_passport_expiry'],
                        'paci' => isset($input['spouse_paci']) ? $input['spouse_paci'] : null,
                        'sndp_branch' => isset($input['sndp_branch']) ? $input['sndp_branch'] : null,
                        'sndp_branch_number' => isset($input['sndp_branch_number']) ? $input['sndp_branch_number'] : null,
                        'sndp_union' => isset($input['sndp_union']) ? $input['sndp_union'] : null,
                        'completed' => 0
                    ]
                );
                // Updating members table (Already created an entry when registering username)
                Member::where('user_id', $spouse_user->id)->update([
                    'gender' => $input['spouse_gender'],
                    'blood_group' => $input['spouse_blood_group'],
                    'type' => 'spouse'
                ]);
                //Updating users table - phone number and avatar
                User::where('id', $spouse_user->id)->update([
                    'phone' => $input['spouse_phone'],
                    'calling_code' => $input['spouse_calling_code'],
                    'avatar' => $spouse_avatarName,
                ]);
                // Create membership table entry
                Membership::create([
                    'user_id' => $spouse_user->id,
                    'type' => $input['type'],
                    'introducer_name' => isset($input['introducer_name']) ? $input['introducer_name'] : null,
                    'introducer_phone' => $introducer_country_code.$introducer_phone,
                    'introducer_mid' => isset($input['introducer_mid']) ? $input['introducer_mid'] : null,
                    'introducer_unit' => isset($input['introducer_unit']) ? $input['introducer_unit'] : null,
                ]);
                // Create contacts table entry
                MemberLocalAddress::create([
                    'user_id' => $spouse_user->id,
                    'governorate' => $input['governorate'],
                    'line_1' => $input['local_address_area'],
                    'building' => isset($input['local_address_building']) ? $input['local_address_building'] : null,
                    'flat' => isset($input['local_address_flat']) ? $input['local_address_flat'] : null,
                    'floor' => isset($input['local_address_floor']) ? $input['local_address_floor'] : null,
                ]);
                // Adding introducers details
                MemberPermanentAddress::create([
                    'user_id' => $spouse_user->id,
                    'line_1' => $input['permanent_address_line_1'],
                    'line_2' => isset($input['permanent_address_line_2']) ? $input['permanent_address_line_2'] : null,
                    'district' => isset($input['permanent_address_district']) ? $input['permanent_address_district'] : null,
                    'contact' => $permanent_cCod.$permanent_phone,
                ]);
                
                //Adding relationship
                $relation = MemberEnum::where('type', 'relationship')->where('slug', 'spouse')->first();
                $mainMember = Member::where('user_id',$user->id)->first();
                MemberRelation::create([
                    'member_id' => $mainMember->id,
                    'related_member_id' => $spouse_member->id,
                    'relationship_id' => $relation->id,
                ]);
                MemberRelation::create([
                    'member_id' => $spouse_member->id,
                    'related_member_id' => $mainMember->id,
                    'relationship_id' => $relation->id,
                ]);
            }
            DB::commit();

            $idQr = false;
            $profileCompleted = false;
            $pendingApproval = false;
            $activeMembership = false;
            $currentStatus = null;
            $proofPending = false;
            $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedMember.user', 'relations.relatedMember.membership', 'relations.relatedMember.details', 'relations.relatedDependent', 'requests', 'committees', 'trustee'])->where('user_id' , $user->id)->first();
            // Checking id card proof is uploaded; Use case: a member logged in and the member is just registered and added profile details, but not uploaded proof
            if($member && $member->details){
                $profileCompleted =  true;
                if($member->membership->status !== 'inactive'){
                    if(!$member->details->photo_civil_id_front || $member->details->photo_civil_id_back || $member->details->photo_passport_front || $member->details->photo_passport_back){
                        $proofPending = true;
                    }
                }
            }else{
                $proofPending = true; // in no details, proof also pending normally
            }

            $statuses = requestStatusDisplay($user->id);
            $currentStatus = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
            if($currentStatus){
                $pendingApproval = $currentStatus->request_status->slug === 'confirmed' ? false : true;
            }
            //Member ID
            if($member->membership){
                $activeMembership = $member->membership->status === 'active' ? true : false;
                $idQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
                $member->membership->qrCode = 'data:image/png;base64, ' . base64_encode($idQr);
            }
            $member->user->avatar = url('storage/images/'. $member->user->avatar);

            
            if($member->relations){
                foreach($member->relations as $key => $relative){
                    if($relative->related_member_id){
                        $member->relations[$key]->relatedMember->user->avatar = url('storage/images/'. $member->relations[$key]->relatedMember->user->avatar);
                        if($relative->relatedMember->active){
                            $spouseIdQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedMember->name,  'Membership ID' => $member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $member->relations[$key]->relatedMember->details->civil_id]));
                            $member->relations[$key]->relatedMember->membership->qrCode = 'data:image/png;base64, ' . base64_encode($spouseIdQr);
                        }
                    }else if($relative->related_dependent_id){
                        $member->relations[$key]->relatedDependent->avatar = url('storage/images/'. $member->relations[$key]->relatedDependent->avatar);
                    }
                    
                }
            }

            $proofPendingTypes = [];
            if($proofPending){
                $proofPendingTypes[] = 'self';
                if($member->membership->type === 'family'){
                    $proofPendingTypes[] = 'spouse';
                }
            }
            
            $response = [
                'success' => true,
                'is_member' => true,
                'profile_completed' => $profileCompleted,
                'active_membership' => $activeMembership,
                'pending_approval' => $pendingApproval,
                'current_status' => $currentStatus,
                'proof_pending' => $proofPending,
                'proof_pending_types' => $proofPendingTypes,
                'family_request' => $member->membership->type === 'family' ? true : false,
                'user' => $user,
                'member' => $member,
                'statuses' => $statuses,
            ];
            if($input['type'] === 'family'){
                $response['spouse'] = $spouse_user;
            }
            return $this->sendResponse($response, 'Your member details added successfully.');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed adding member details', $e);
        }
    }

    public function uploadProof(Request $request)
    {
        $user = Auth::user();
        $existing_membership = Membership::where('user_id', $user->id)->first();
        if(!$existing_membership){
            return $this->sendError('Not allowed', 'You are not a member', 405); 
        }

        $requestingMember = Member::where('user_id', $user->id)->first();
        $spouseUser = null;
        $spouseMember = null;
        $hasSpouse = MemberRelation::where('related_member_id', $requestingMember->id)->first();
        if($hasSpouse){
            $spouseMember = Member::where('member_id', $hasSpouse->member_id)->first();
            $spouseUser = User::where('id', $spouseMember->user_id)->first();
        }

        $proofPendingTypes = $request->proofTypes;
        if($proofPendingTypes){
            if(in_array('self', $proofPendingTypes)){
                $rules['photo_civil_id_front']    = ['required'];
                $rules['photo_civil_id_back']     = ['required'];
                $rules['photo_passport_front']    = ['required'];
                $rules['photo_passport_back']     = ['required'];
                
                $messages['photo_civil_id_front.required']    = 'Required field';
                $messages['photo_civil_id_back.required']    = 'Required field';
                $messages['photo_passport_front.required']    = 'Required field';
                $messages['photo_passport_back.required']    = 'Required field';
                
            }
            if(in_array('spouse', $proofPendingTypes)){
                $rules['spouse_photo_civil_id_front']    = ['required'];
                $rules['spouse_photo_civil_id_back']     = ['required'];
                $rules['spouse_photo_passport_front']    = ['required'];
                $rules['spouse_photo_passport_back']     = ['required'];

                $messages['spouse_photo_civil_id_front.required']    = 'Required field';
                $messages['spouse_photo_civil_id_back.required']    = 'Required field';
                $messages['spouse_photo_passport_front.required']    = 'Required field';
                $messages['spouse_photo_passport_back.required']    = 'Required field';
            }

        }
        $validator = Validator::make($request->all(), $rules,$messages);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }
        
        $input = $request->all();
        DB::beginTransaction();
        try {
            
            if($proofPendingTypes && in_array('self', $proofPendingTypes)){
                // Storing files
                $civil_id_front_name = 'cvf'.$user->id.'_'.time().'.'.mime2ext($input['photo_civil_id_front']); 
                $civil_id_back_name = 'cvb'.$user->id.'_'.time().'.'.mime2ext($input['photo_civil_id_back']); 
                $passport_front_name = 'ppf'.$user->id.'_'.time().'.'.mime2ext($input['photo_passport_front']); 
                $passport_back_name = 'ppb'.$user->id.'_'.time().'.'.mime2ext($input['photo_passport_back']); 
                Storage::put('public/images/'.$civil_id_front_name, base64_decode($input['photo_civil_id_front']));
                Storage::put('public/images/'.$civil_id_back_name, base64_decode($input['photo_civil_id_back']));
                Storage::put('public/images/'.$passport_front_name, base64_decode($input['photo_passport_front']));
                Storage::put('public/images/'.$passport_back_name, base64_decode($input['photo_passport_back']));

                //Adding proof data to Membership detail table
                MemberDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'photo_civil_id_front' => $civil_id_front_name,
                        'photo_civil_id_back' => $civil_id_back_name,
                        'photo_passport_front' => $passport_front_name,
                        'photo_passport_back' => $passport_back_name,
                        'completed' => 1
                    ]
                );
                // Adding entry to membership_request table, with 'saved' status;
                // 1. Adding SAVED status in Membership request table
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
                MembershipRequest::create([
                    'user_id' => $user->id,
                    'request_status_id' => $status->id,
                    'checked' => 1, 
                    'updated_by' => $user->id,
                ]);
                
                // 2. Adding SUBMITTED status in Membership request table
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                MembershipRequest::create([
                    'user_id' => $user->id,
                    'request_status_id' => $status->id,
                    'updated_by' => $user->id,
                ]);
            }
            
            if($proofPendingTypes && in_array('spouse', $proofPendingTypes)){
                // Storing spouse files
                $spouse_civil_id_front_name = 'cvf'.$spouseUser->id.'_'.time().'.'.mime2ext($input['spouse_photo_civil_id_front']); 
                $spouse_civil_id_back_name = 'cvb'.$spouseUser->id.'_'.time().'.'.mime2ext($input['spouse_photo_civil_id_back']); 
                $spouse_passport_front_name = 'ppf'.$spouseUser->id.'_'.time().'.'.mime2ext($input['spouse_photo_passport_front']);  
                $spouse_passport_back_name = 'ppb'.$spouseUser->id.'_'.time().'.'.mime2ext($input['spouse_photo_passport_back']); 
                Storage::put('public/images/'.$spouse_civil_id_front_name, base64_decode($input['spouse_photo_civil_id_front']));
                Storage::put('public/images/'.$spouse_civil_id_back_name, base64_decode($input['spouse_photo_civil_id_back']));
                Storage::put('public/images/'.$spouse_passport_front_name, base64_decode($input['spouse_photo_passport_front']));
                Storage::put('public/images/'.$spouse_passport_back_name, base64_decode($input['spouse_photo_passport_back']));
                // Updating spouse file data
                MemberDetail::updateOrCreate(
                    ['user_id' => $spouseUser->id],
                    [
                        'photo_civil_id_front' => $spouse_civil_id_front_name,
                        'photo_civil_id_back' => $spouse_civil_id_back_name,
                        'photo_passport_front' => $spouse_passport_front_name,
                        'photo_passport_back' => $spouse_passport_back_name,
                        'completed' => 1
                    ]
                );
                // Adding entry to membership_request table, with 'saved' status;
                // 1. Adding SAVED status in Membership request table
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
                MembershipRequest::create([
                    'user_id' => $spouseUser->id,
                    'request_status_id' => $status->id,
                    'checked' => 1, 
                    'updated_by' => $spouseUser->id,
                ]);
                
                // 2. Adding SUBMITTED status in Membership request table
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                MembershipRequest::create([
                    'user_id' => $spouseUser->id,
                    'request_status_id' => $status->id,
                    'updated_by' => $spouseUser->id,
                ]);
            }
            DB::commit();
            
            $pendingApproval = false;
            $statuses = requestStatusDisplay($user->id);
            $currentStatus = MembershipRequest::where('user_id', $user->id)->latest('id')->first();
            if($currentStatus){
                $pendingApproval = $currentStatus->request_status->slug === 'confirmed' ? false : true;
            }
            
            $response = [
                'success' => true,
                'profile_completed' => true,
                'active_membership' => false,
                'pending_approval' => $pendingApproval,
                'current_status' => $currentStatus,
                'proof_pending' => false,
                'proof_pending_types' => [],
                'statuses' => $statuses,
            ];
            return $this->sendResponse($response, 'Your document proof successfully and the Membership request sent to verification');
        }catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed adding member details', $e);
        }
    }

    protected function validationRules($request)
    {
        $user = Auth::user();
        $rules =  [
            'member_unit_id'    => ['required', Rule::exists(MemberUnit::class, 'id')],
            'phone'             => 'required|unique:users,phone,'.$user->id,
            'calling_code'      => ['required'],
            'whatsapp'          => ['required', 'numeric'],
            'whatsapp_code'     => ['required'],
            'emergency_phone'   => ['required', 'numeric'],
            'emergency_phone_code' => ['required'],
            'gender'            => ['required', 'string'],
            'dob'               => ['required', 'date_format:Y-m-d'],
            'blood_group'       => ['required', 'string'],
            'civil_id'          => ['required', 'string'],
            'passport_no'       => ['required', 'string'],
            'passport_expiry'   => ['required', 'date_format:Y-m-d'],
            'governorate'        => ['required', 'string'],
            'local_address_area'=> ['required', 'string'],
            'type'              => ['required', 'string'],
            'photo'             => ['required']
        ];

        $messages = [
            'member_unit_id.required' => 'Unit is required',
            'member_unit_id.exists' => 'Invalid Unit ID',
            'phone.required' => 'Phone is required',
            'phone.unique' => 'phone already used',
            'calling_code.required' => 'Required',
            'whatsapp.required' => 'Whatsapp is required',
            'whatsapp.numeric' => 'Should be a number',
            'whatsapp_code.required' => 'Required',
            'emergency_phone.required' => 'Emergency contact is required',
            'emergency_phone.numeric' => 'Should be a number',
            'emergency_phone_code.required' => 'Required',
            'gender.required' => 'Gender is required',
            'dob.required' => 'DOB is required',
            'dob.date_format' => 'Should be Y-m-d format',
            'blood_group.required' => 'Blood group is required',
            'civil_id.required' => 'Civil ID is required',
            'civil_id.string' => 'Invalid Civil ID',
            'passport_no.required' => 'Passport no. is required',
            'passport_expiry.required' => 'Expiry date is required',
            'passport_expiry.date_format' => 'Should be Y-m-d format',
            'governorate.required' => 'Governorate is required',
            'local_address_area.required' => 'Area is required',
            'type.required' => 'Membership type is required',
            'photo.required' => 'Photo is required'
        ];

        if($request->type === 'family'){
            $rules['spouse_name'] = ['required', 'string'];
            $rules['spouse_email'] = ['required', Rule::unique(User::class, 'email')];
            $rules['spouse_phone'] = ['required', Rule::unique(User::class, 'phone')];
            $rules['spouse_calling_code'] = ['required'];
            $rules['spouse_whatsapp'] = ['required', 'numeric'];
            $rules['spouse_whatsapp_code'] = ['required'];
            $rules['spouse_emergency_phone'] = ['required', 'numeric'];
            $rules['spouse_emergency_phone_code'] = ['required'];
            $rules['spouse_gender'] = ['required', 'string'];
            $rules['spouse_dob'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_blood_group'] = ['required', 'string'];
            $rules['spouse_civil_id'] = ['required', 'string'];
            $rules['spouse_passport_no'] = ['required', 'string'];
            $rules['spouse_passport_expiry'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_photo'] = ['required'];

            $messages['spouse_name.required'] = 'Name is required';
            $messages['spouse_email.required'] = 'Email is required';
            $messages['spouse_email.unique'] = 'Email already registered';
            $messages['spouse_phone.required'] = 'Phone is required';
            $messages['spouse_phone.unique'] = 'Number already used';
            $messages['spouse_calling_code.required'] = 'Required';
            $messages['spouse_whatsapp.required'] = 'Whatsapp is required';
            $messages['spouse_whatsapp.numeric'] = 'Whatsapp number should be a number';
            $messages['spouse_whatsapp_code.required'] = 'Required';
            $messages['spouse_emergency_phone.required'] = 'Emergency Phone required';
            $messages['spouse_emergency_phone.numeric'] = 'Should be a number';
            $messages['spouse_emergency_phone_code.required'] = 'Required';
            $messages['spouse_gender.required'] = 'Gender is required';
            $messages['spouse_dob.required'] = 'DOB is required';
            $messages['spouse_dob.date_format'] = 'Should be Y-m-d format';
            $messages['spouse_blood_group.required'] = 'Blood group is required';
            $messages['spouse_civil_id.required'] = 'Civil ID is required';
            $messages['spouse_civil_id.string'] = 'Invalid Civil ID';
            $messages['spouse_passport_no.required'] = 'Passport no. is required';
            $messages['spouse_passport_expiry.required'] = 'Expiry date is required';
            $messages['spouse_passport_expiry.date_format'] = 'Should be Y-m-d format';
            $messages['spouse_photo.required'] = 'Photo is required';
        }

        return [
            $rules,
            $messages
        ];
    }
    
}
