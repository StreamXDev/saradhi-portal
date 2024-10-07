<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Members\Models\MemberEnum;

class DependentController extends Controller
{
    
    public function createDependent()
    {
        $countries = Country::with('regions')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $data = [
            'countries' => $countries,
            'blood_groups' => $blood_groups
        ];
        return $this->sendResponse($data);
    }

    // storing dependent data
    public function storeDependent(Request $request)
    {
        $user = Auth::user();
        
    }
}
