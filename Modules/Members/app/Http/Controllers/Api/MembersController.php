<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberContact;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Models\MemberUnit;

class MembersController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:profile.view', ['only' => ['showProfile']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        return $this->sendResponse(['test']);
    }
    
    /**
     * Send the form for creating a new resource.
     */
    public function createDetails()
    {
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = [
            ['name'=>'Male', 'slug' => 'male'], 
            ['name' => 'Female', 'slug' => 'female']
        ];
        return $this->sendResponse(compact('units', 'blood_groups', 'gender'));
    }


    /**
     * Send the form for creating a new resource.
     */
    public function storeDetails(Request $request)
    {
        $user = Auth::user();

        $membership = Membership::where('user_id', $user->id)->first();
        $member_request = MembershipRequest::where('user_id', $user->id)->latest()->first();

        if($membership){
            return $this->sendError('Not allowed.', 'You are already a member. Please try to Update Profile option if you want to update data', 405); 
        }

        if($member_request){
            return $this->sendError('Already requested.', 'Your membership '.strtolower($member_request->request_status->description), 405); 
        }

        $validator = Validator::make($request->all(), ...$this->validationRules());
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $avatarName = 'av'.$user->id.'_'.time().'.'.$request->avatar->extension(); 
        $request->avatar->storeAs('images', $avatarName);

        $civil_id_front_name = 'cvf'.$user->id.'_'.time().'.'.$request->photo_civil_id_front->extension(); 
        $request->photo_civil_id_front->storeAs('images', $civil_id_front_name);

        $civil_id_back_name = 'cvb'.$user->id.'_'.time().'.'.$request->photo_civil_id_back->extension(); 
        $request->photo_civil_id_back->storeAs('images', $civil_id_back_name);

        $passport_front_name = 'ppf'.$user->id.'_'.time().'.'.$request->photo_passport_front->extension(); 
        $request->photo_passport_front->storeAs('images', $passport_front_name);

        $passport_back_name = 'ppb'.$user->id.'_'.time().'.'.$request->photo_passport_back->extension(); 
        $request->photo_passport_back->storeAs('images', $passport_back_name);

        $input = $request->all();

        DB::beginTransaction();

        MemberDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'member_unit_id' => $input['member_unit_id'],
                'civil_id' => $input['civil_id'],
                'photo_civil_id_front' => $civil_id_front_name,
                'photo_civil_id_back' => $civil_id_back_name,
                'dob' => $input['dob'],
                'company' => $input['company'],
                'profession' => $input['profession'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'photo_passport_front' => $passport_front_name,
                'photo_passport_back' => $passport_back_name,
                'completed' => 1
            ]
        );

        Member::where('user_id', $user->id)->update([
            'gender' => $input['gender'],
            'blood_group' => $input['blood_group']
        ]);

        //Add phone to users and member contact table
        User::where('id', $user->id)->update([
            'phone' => $input['phone'],
            'avatar' => $avatarName,
        ]);

        $contact_types = MemberEnum::where('type', 'contact_type')->where('slug', 'phone')->first();

        MemberContact::create([
            'user_id' => $user->id,
            'contact_type_id' => $contact_types->id,
            'title' => $contact_types->name,
            'value' => $input['phone']
        ]);

        //Getting status data
        $status = MemberEnum::where('type', 'request_status')->where('order', 0)->first();

        MembershipRequest::create([
            'user_id' => $user->id,
            'request_status_id' => $status->id,
            'type' => $input['type'],
            'updated_by' => $user->id
        ]);

        //TODO: [Phase 2] get notified the users who permitted to view new membership requests

        DB::commit();

        $response = [
            'request_status' => $status->name,
            'description' => $status->description
        ];
        
        return $this->sendResponse($response, 'Your membership request has been sent for review.');

    }

    //add address
    //add family member

    protected function validationRules()
    {
        $rules =  [
            'member_unit_id'    => ['required', Rule::exists(MemberUnit::class, 'id')],
            'civil_id'          => ['required', 'string'],
            'dob'               => ['required', 'date_format:Y-m-d'],
            'company'           => ['nullable', 'string'],
            'profession'        => ['nullable', 'string'],
            'passport_no'       => ['required', 'string'],
            'passport_expiry'   => ['required', 'date_format:Y-m-d'],
            'gender'            => ['required', 'string'],
            'blood_group'       => ['required', 'string'],
            'type'              => ['required', 'string'],
            'avatar'            => ['required','image|mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'civil_id_front'    => ['required','image|mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'civil_id_back'     => ['required','image|mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'passport_front'    => ['required','image|mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'passport_back'     => ['required','image|mimes:jpeg,png,jpg,gif,svg','max:2048'],
        ];

        $messages = [
            'member_unit_id.required' => 'Unit is required',
            'member_unit_id.exists' => 'Unit is not valid',
            'civil_id.required' => 'Civil ID is required',
            'civil_id.string' => 'Civil ID is not valid',
            'dob.required' => 'Date of birth is required',
            'dob.date_format' => 'Date of birth should be of format Y-m-d',
            'passport_no.required' => 'Passport number is required',
            'passport_expiry.required' => 'Passport expiry date is required',
            'passport_expiry.date_format' => 'Passport expiry date should be of format Y-m-d',
            'gender.required' => 'Gender is required',
            'blood_group.required' => 'Blood group is required',
            'type.required' => 'Membership type is required',
            
            'avatar.required' => 'Profile photo is required',
            'avatar.image' => 'Profile photo should be an image',
            'avatar.max' => 'Profile photo size should not be exceeded more than 2mb',
            
            'civil_id_front.required' => 'Civil Id copy (Front) is required',
            'civil_id_front.image' => 'Civil Id copy (Front) should be an image',
            'civil_id_front.max' => 'Civil Id copy (Front) file size should not be exceeded more than 2mb',
            'civil_id_back.required' => 'Civil Id copy (Back) is required',
            'civil_id_back.image' => 'Civil Id copy (Back) should be an image',
            'civil_id_back.max' => 'Civil Id copy (Back) file size should not be exceeded more than 2mb',
            
            'passport_front.required' => 'Passport copy (Front) is required',
            'passport_front.image' => 'Passport copy (Front) should be an image',
            'passport_front.max' => 'Passport copy (Front) file size should not be exceeded more than 2mb',
            'passport_back.required' => 'Passport copy (Back) is required',
            'passport_back.image' => 'Passport copy (Back) should be an image',
            'passport_back.max' => 'Passport copy (Back) file size should not be exceeded more than 2mb',
        ];

        return [
            $rules,
            $messages
        ];
    }
    
}
