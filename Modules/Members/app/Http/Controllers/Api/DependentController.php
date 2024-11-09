<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MemberUnit;

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
        $existing_membership_data = Membership::where('user_id', $user->id)->first();
        return $this->sendResponse(["type" => $request->type]);
        
    }

    protected function validationRules($request)
    {
        $user = Auth::user();
        
        if($request->type === 'child')

        if($request->type === 'spouse'){
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
