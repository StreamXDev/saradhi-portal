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
use Modules\Members\Models\MemberDependent;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MemberUnit;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DependentController extends BaseController
{
    
    public function create()
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
    public function store(Request $request)
    {
        $user = Auth::user();

        $idQr = false;
        $profileCompleted = false;
        $pendingApproval = false;
        $activeMembership = false;
        $currentStatus = null;
        $proofPending = false;

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
        $validator = Validator::make($request->all(), ...$this->validationRules($request));
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
                'email' => $input['email'],
                'calling_code' => $input['calling_code'],
                'phone' => $input['phone'],
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group'],
                'civil_id' => $input['civil_id'],
                'dob' => $input['dob'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'parent_user_id' => $user->id,
                'parent_user_mid' => $requesting_member->membership->mid,
                'type' => 'child'
            ];
            DB::beginTransaction();
            $child = MemberDependent::create($childInput);
            $child_avatar = 'av'.$child->id.'_'.time().'.'.mime2ext($input['avatar_mime']);
            Storage::put('public/images/'.$child_avatar, base64_decode($input['avatar']));
            MemberDependent::where('id', $child->id)->update([
                'avatar' => $child_avatar,
            ]);

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
                    $rd = MemberDependent::where('id', $primary_relations->related_dependent_id);
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
                        'dependent_id' => $parent_spouse,
                        'related_dependent_id' => $child->id,
                        'relationship_id' => $sibling_relation_type->id,
                    ]);
                    MemberRelation::create([
                        'related_dependent_id' => $parent_spouse,
                        'dependent_id' => $child->id,
                        'relationship_id' => $sibling_relation_type->id,
                    ]);
                }
            }

            DB::commit();
        }

        if($requesting_member->relations){
            foreach($requesting_member->relations as $key => $relative){
                if($relative->related_member_id){
                    $requesting_member->relations[$key]->relatedMember->user->avatar = url('storage/images/'. $requesting_member->relations[$key]->relatedMember->user->avatar);
                    if($relative->relatedMember->active){
                        $spouseIdQr = QrCode::format('png')->size(300)->generate(json_encode(['Name' =>  $requesting_member->relations[$key]->relatedMember->name,  'Membership ID' => $requesting_member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $requesting_member->relations[$key]->relatedMember->details->civil_id]));
                        $requesting_member->relations[$key]->relatedMember->membership->qrCode = 'data:image/png;base64, ' . base64_encode($spouseIdQr);
                    }
                }else if($relative->related_dependent_id){
                    $requesting_member->relations[$key]->relatedDependent->avatar = url('storage/images/'. $requesting_member->relations[$key]->relatedDependent->avatar);
                }
                
            }
        }
    }

    protected function validationRules($request)
    {
        
        if($request->type === 'child'){
            $rules['name'] = ['required', 'string'];
            $rules['email'] = [Rule::unique(MemberDependent::class, 'email')];
            $rules['phone'] = [Rule::unique(MemberDependent::class, 'phone')];
            $rules['gender'] = ['required', 'string'];
            $rules['dob'] = ['required', 'date_format:Y-m-d'];
            $rules['blood_group'] = ['required', 'string'];
            $rules['civil_id'] = ['required', 'string'];
            $rules['passport_no'] = ['required', 'string'];
            $rules['passport_expiry'] = ['required', 'date_format:Y-m-d'];
            $rules['photo'] = ['required'];

            $messages['name.required'] = 'Name is required';
            $messages['email.unique'] = 'Email already registered';
            $messages['phone.unique'] = 'Number already used';
            $messages['gender.required'] = 'Gender is required';
            $messages['dob.required'] = 'DOB is required';
            $messages['dob.date_format'] = 'Should be Y-m-d format';
            $messages['blood_group.required'] = 'Blood group is required';
            $messages['civil_id.required'] = 'Civil ID is required';
            $messages['civil_id.string'] = 'Invalid Civil ID';
            $messages['passport_no.required'] = 'Passport no. is required';
            $messages['passport_expiry.required'] = 'Expiry date is required';
            $messages['passport_expiry.date_format'] = 'Should be Y-m-d format';
            $messages['photo.required'] = 'Photo is required';
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
            $rules['photo'] = ['required'];

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
            $messages['photo.required'] = 'Photo is required';
        }

        return [
            $rules,
            $messages
        ];
    }
}
