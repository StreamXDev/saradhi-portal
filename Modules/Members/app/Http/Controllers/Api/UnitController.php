<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberUnit;

class UnitController extends BaseController
{
    /**
     * Unit change request
     */
    public function cuRequest(Request $request)
    {
        
        /**
         * check active member
         * check active membership
         * get current unit and unit secretary
         * get preferred unit and unit secretary
         * add request
         * send notification & mail to current unit secretary
         */
        $user = Auth::user();
        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'preferred_unit' => 'required'
        ],[
            'preferred_unit.required' => 'Preferred Unit is required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }

        $member = Member::with('details')->where('user_id', $user->id)->first();
        $current_unit = MemberUnit::where('id', $input['current_unit'])->first();
        $preferred_unit = MemberUnit::where('id', $input['preferred_unit'])->first();



    }

    public function changeStatusCuRequest()
    {
        /**
         * 
         */
    }
}
