<?php

namespace Modules\Imports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Imports\Models\Export;
use Modules\Imports\Models\Import;
use Modules\Imports\Models\Membership as ImportMembership;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDependent;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MemberTrustee;
use Modules\Members\Models\MemberUnit;

class ImportMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('imports::index');
    }


    public function import()
    {
        $last_exported  = Export::select('membership_id')->latest()->first();

        if($last_exported){
            $importedMemberships = ImportMembership::with('primary_member', 'type', 'status', 'members', 'members.details', 'members.contacts', 'members.addresses', 'members.addresses.country', 'members.addresses.region', 'members.type', 'members.gender', 'members.membership', 'members.membership.unit', 'members.trustee')->where('id', '>', $last_exported->membership_id)->limit(1)->get();
        }else{
            $importedMemberships = ImportMembership::with('primary_member', 'type', 'status', 'members', 'members.details', 'members.contacts', 'members.addresses', 'members.addresses.country', 'members.addresses.region', 'members.type', 'members.gender', 'members.membership', 'members.membership.unit', 'members.trustee')->limit(1)->get();
        }
        
        //dd($importedMemberships);
        foreach($importedMemberships as $importedMembership){
            $importedMembers = $importedMembership->members;
            foreach($importedMembers as $importedMember){
                $exported = false;
                $remark = null;

                DB::beginTransaction();
                if($importedMember->sub_id == null){ //If the member is not a child
                    // ----------------- Creating User ------------------- //
                    // Checking User exists
                    $user_exists = User::where('email', $importedMember->email)->first();
                    if($user_exists){
                        $remark = 'The email already used for: '.$user_exists->name.'('.$user_exists->id.')';
                    }else{
                        // creating user
                        $user_data = [
                            'name' => $importedMember->name,
                            'email' => $importedMember->email,
                            'password' => Hash::make(Str::random(10)),
                            'phone' => $importedMember->mobile,
                            'calling_code' => $importedMember->calling_code,
                            'email_verified_at' => now()
                        ];
                        $user = User::create($user_data);
                        $user->assignRole(['Member']);

                        // adding avatar
                        if($importedMember->details->photo){
                            $avatar = 'av'.$user->id.'_'.time().'.jpg';
                            Storage::put('public/images/'.$avatar, base64_decode($importedMember->details->photo));
                            User::where('id', $user->id)->update([
                                'avatar' => $avatar,
                            ]);
                        }

                        // Adding Member
                        $member_data = [
                            'user_id' => $user->id,
                            'type' => $importedMember->type->code,
                            'name' => $importedMember->name,
                            'gender' => $importedMember->gender->code,
                            'blood_group' => $importedMember->details->blood_group->name,
                            'active' => 1
                        ];
                        $new_member = Member::create($member_data);

                        // Adding member details
                        $unit = null;
                        if($importedMember->membership->unit){
                            $unit = MemberUnit::where('slug', $importedMember->membership->unit->code)->first();
                        }
                        $memberDetails_data = [
                            'user_id' => $user->id,
                            'member_unit_id' => $unit->id,
                            'civil_id' => $importedMember->civil_id,
                            'dob' => $importedMember->details->dob,
                            'company' => $importedMember->details->company,
                            'profession' => $importedMember->details->professional,
                            'passport_no' => $importedMember->details->passport_no,
                            'passport_expiry' => $importedMember->details->passport_expiry,
                            'completed' => 1
                        ];
                        MemberDetail::create($memberDetails_data);

                        // Adding local Addresses

                        foreach($importedMember->addresses as $address){
                            if($address->type->code == 'local'){
                                $local_address_data = [
                                    'user_id' => $user->id,
                                    'line_1' => $address->address_1,
                                    'building' => $address->address_2,
                                    'city' => $address->city,
                                    'country' => $address->country->name,
                                    'region' => $address->region->name,
                                    'zip' => $address->zip
                                ];
                                MemberLocalAddress::create($local_address_data);
                            }
                        }
                        // Adding Indian Addresses
                        foreach($importedMember->addresses as $address){
                            if($address->type->code == 'indian'){
                                $permanent_address_data = [
                                    'user_id' => $user->id,
                                    'line_1' => $address->address_1,
                                    'line_2' => $address->address_2,
                                    'city' => $address->city,
                                    'country' => $address->country->name,
                                    'region' => $address->region->name,
                                    'zip' => $address->zip
                                ];
                                MemberPermanentAddress::create($permanent_address_data);
                            }
                        }

                        // Adding membership
                        $membership_data = [
                            'mid' => $importedMember->membership->mid,
                            'user_id' => $user->id,
                            'start_date' => $importedMember->membership->joining_date,
                            'updated_date' => $importedMember->membership->joining_date,
                            'expiry_date' => $importedMember->membership->expiry_date,
                            'type' => $importedMembership->type->code,
                            'status' => $importedMembership->status->code,
                            'joined_as' => 'old'
                        ];
                        $new_membership = Membership::create($membership_data);

                        // Adding trustee
                        if($importedMember->trustee){
                            $trustee_data = [
                                'user_id' => $user->id,
                                'tid' => $importedMember->trustee->tid,
                                'title' => $importedMember->trustee->title,
                                'joining_date' => $importedMember->trustee->joining_date,
                                'status' => 'active'
                            ];
                            MemberTrustee::create($trustee_data);
                        }

                        // Adding relationship
                        $this->add_relationship($new_member, $new_membership);

                        $exported = true;
                        // adding status to imports table
                        Import::create([
                            'user_id' => $user->id,
                            'imported' => 1,
                        ]);
                    }
                }else{
                    // create dependent
                    $mid = $importedMember->membership->mid;
                    $existing_member_with_mid = Membership::with('member')->where('mid', $mid)->get();
                    $primary_member = null;
                    foreach($existing_member_with_mid as $existing_membership){
                        if($existing_membership->member->type == 'primary'){
                            $primary_member = $existing_membership;
                        }
                    }
                    $dependent_data = [
                        'parent_user_id' => $primary_member->user_id,
                        'parent_mid' => $mid,
                        'type' => $importedMember->type->code,
                        'name' => $importedMember->name,
                        'email' => $importedMember->email,
                        'phone' => $importedMember->mobile,
                        'calling_code' => $importedMember->calling_code,
                        'gender' => $importedMember->gender->code,
                        'blood_group' => $importedMember->details->blood_group->name,
                        'civil_id' => $importedMember->civil_id,
                        'dob' => $importedMember->details->dob,
                        'passport_no' => $importedMember->details->passport_no,
                        'passport_expiry' => $importedMember->details->passport_expiry,
                    ];
                    $dependent = MemberDependent::create($dependent_data);
                    // adding avatar
                    if($importedMember->details->photo){
                        $avatar = 'av'.$dependent->id.'_'.time().'.jpg';
                        Storage::put('public/images/'.$avatar, base64_decode($importedMember->details->photo));
                        MemberDependent::where('id', $dependent->id)->update([
                            'avatar' => $avatar,
                        ]);
                    }

                    // adding relationship
                    $this->add_relationship(false, false, $dependent);

                    // adding status to imports table
                    Import::create([
                        'dependent_id' => $dependent->id,
                        'imported' => 1,
                    ]);


                }
    
                Export::create([
                    'mid' => $importedMember->id,
                    'membership_id' => $importedMember->membership_id,
                    'name' => $importedMember->name,
                    'exported' => $exported,
                    'remark' => $remark
                ]);

                DB::commit();
            }
        }

        return view('imports::index');
        
    }

    protected function add_relationship($new_member, $new_membership, $dependent = null){
        // Adding relationship
        // all relation types
        $relation_types = MemberEnum::where('type', 'relationship')->get()->toArray();

        if($dependent){
            $existing_members_with_mid = Membership::with('member')->where('mid', $dependent->parent_mid)->get();
            $existing_dependent_with_mid = MemberDependent::where('parent_mid', $dependent->parent_mid)->get();

            foreach($existing_members_with_mid as $existing_membership){
                $existing_relation_against_member_id = MemberRelation::where('member_id', $existing_membership->member->id)->where('related_dependent_id', $dependent->id);

                if(!$existing_relation_against_member_id){
                    $relationship_id = $this->get_relationship_id($dependent, $relation_types, $existing_membership);
                    
                    // if both are siblings, setting ids to dependent and related dependent columns
                    MemberRelation::create([
                        'member_id' => $existing_membership->member->id,
                        'related_dependent_id' => $dependent->id,
                        'relationship_id' => $relationship_id['first_relation']
                    ]);
                    MemberRelation::create([
                        'dependent_id' => $dependent->id,
                        'related_member_id' => $existing_membership->member->id,
                        'relationship_id' => $relationship_id['second_relation']
                    ]);
                }
            }

            
            foreach($existing_dependent_with_mid as $existing_dependent){
                $existing_relation_against_dependent_id = MemberRelation::where('dependent_id', $existing_dependent->id)->where('related_dependent_id', $dependent->id)->first();
                
                if(!$existing_relation_against_dependent_id){
                    //$existing_membership->member->type
                    $existing_dependent_type = array('member' => array('type' => 'child')); // hack for the 'get_relationship_id' function
                    $relationship_id = $this->get_relationship_id($dependent, $relation_types, $existing_dependent_type);
                    
                    // if both are siblings, setting ids to dependent and related dependent columns
                    MemberRelation::create([
                        'dependent_id' => $existing_membership->member->id,
                        'related_dependent_id' => $dependent->id,
                        'relationship_id' => $relationship_id['first_relation']
                    ]);
                    MemberRelation::create([
                        'dependent_id' => $dependent->id,
                        'related_dependent_id' => $existing_membership->member->id,
                        'relationship_id' => $relationship_id['second_relation']
                    ]);
                }
            }
            
        }else{
            if($new_member->type !== 'primary'){
                $mid = $new_membership->mid;
                $existing_members_with_mid = Membership::with('member')->where('mid', $mid)->get();
                foreach($existing_members_with_mid as $existing_membership){
                    // check relation if added already
                    $existing_relation_against_member_id = MemberRelation::where('member_id', $existing_membership->member->id)->where('related_member_id', $new_member->id);
                    if(!$existing_relation_against_member_id){

                        $relationship_id = $this->get_relationship_id($new_member, $relation_types, $existing_membership);
                        
                        MemberRelation::create([
                            'member_id' => $existing_membership->member->id,
                            'related_member_id' => $new_member->id,
                            'relationship_id' => $relationship_id['first_relation']
                        ]);
                        MemberRelation::create([
                            'member_id' => $new_member->id,
                            'related_member_id' => $existing_membership->member->id,
                            'relationship_id' => $relationship_id['second_relation']
                        ]);
                    }
                }
            }
        }
    }

    protected function get_relationship_id($new_member, $relation_types, $existing_membership){
        $relationship_id = [];
        switch ($new_member->type){
            case 'spouse':
                $relationship_id['first_relation'] = $relationship_id['second_relation'] = $relation_types[array_search('spouse', array_column($relation_types, 'slug'))]['id'];
                break;
            case 'child':
                if($existing_membership->member->type === 'child'){
                    $relationship_id['first_relation'] = $relationship_id['second_relation'] = $relation_types[array_search('sibling', array_column($relation_types, 'slug'))]['id'];
                }else if($existing_membership->member->type === 'primary' || $existing_membership->member->type === 'spouse'){
                    $relationship_id['first_relation'] = $relation_types[array_search('parent', array_column($relation_types, 'slug'))]['id'];
                    $relationship_id['second_relation'] = $relation_types[array_search('child', array_column($relation_types, 'slug'))]['id'];
                }
                break;
        }
        return $relationship_id;
    }
}
