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

        $input = $request->all();

        DB::beginTransaction();

        MemberDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'member_unit_id' => $input['member_unit_id'],
                'civil_id' => $input['civil_id'],
                'dob' => $input['dob'],
                'company' => $input['company'],
                'profession' => $input['profession'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'completed' => 1
            ]
        );

        Member::where('user_id', $user->id)->update([
            'gender' => $input['gender'],
            'blood_group' => $input['blood_group']
        ]);

        //Add phone to users and member contact table
        User::where('id', $user->id)->update([
            'phone' => $input['phone']
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
        ];

        return [
            $rules,
            $messages
        ];
    }
    
}
