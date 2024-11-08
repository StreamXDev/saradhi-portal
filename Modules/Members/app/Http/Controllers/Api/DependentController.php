<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\MemberEnum;

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
        
    }
}
