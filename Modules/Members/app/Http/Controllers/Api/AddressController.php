<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;

class AddressController extends BaseController
{
    
    public function createMemberAddress()
    {
        $countries = Country::with('regions')->where('active', 1)->get();
        $data = [
            'countries' => $countries
        ];
        return $this->sendResponse($data);

    }

    // Storing address
    public function storeMemberAddress(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();
        $type = isset($input['type']) ? $input['type'] : 'local';

        if($type === 'local'){
            $validator = Validator::make($request->all(), [
                'governorate' => 'required|string',
                'local_address_area' => 'required|string',
                'local_address_building' => 'required|string',
                'local_address_flat' => 'required|string',
                'local_address_floor' => 'required|string',
            ],[
                'governorate' => "Governorate is required",
                'local_address_area' => 'Area is required',
                'local_address_building' => 'Building is required',
                'local_address_flat' => 'Flat is required',
                'local_address_floor' => 'Floor is required',
            ]);
        }else if($type === 'indian'){
            $validator = Validator::make($request->all(), [
                'permanent_address_line_1' => 'required|string',
            ],[
                'permanent_address_line_1' => 'Address line 1 is required'
            ]);
        }
        
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()); 
        }
        
        try{
            if($type === 'local'){
                $address = MemberLocalAddress::create([
                    'user_id' => $user->id,
                    'governorate' => $input['governorate'],
                    'country' => $input['country'],
                    'region' => $input['region'],
                    'line_1' => $input['local_address_area'],
                    'building' => $input['local_address_building'],
                    'flat' => $input['local_address_flat'],
                    'floor' => $input['local_address_floor'],
                    'city' => $input['city'],
                    'zip' => $input['zip'],
                ]);
            }else if($type === 'indian'){
                $address = MemberPermanentAddress::create([
                    'user_id' => $user->id,
                    'line_1' => $input['permanent_address_line_1'],
                    'line_2' => $input['permanent_address_line_2'],
                    'country' => $input['country'],
                    'region' => $input['region'],
                    'district' => $input['permanent_address_district'],
                    'city' => $input['city'],
                    'zip' => $input['zip'],
                    'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact'],
                ]);
            }

            $response = [
                'success' => true,
                'address' => $address
            ];
            return $this->sendResponse($response, 'Address added successfully.');
        }catch (\Exception $e) {
            return $this->sendError('Failed', $e);
        }
    }

    // Editing address
    public function updateAddress(Request $request)
    {
        //$address = Address::findOrFail($data['address_id']);
        $user = Auth::user();
        $input = $request->all();
        $type = isset($input['type']) ? $input['type'] : 'local';

        if($type === 'local'){
            $validator = Validator::make($request->all(), [
                'governorate' => 'required|string',
                'local_address_area' => 'required|string',
                'local_address_building' => 'required|string',
                'local_address_flat' => 'required|string',
                'local_address_floor' => 'required|string',
            ],[
                'governorate' => "Governorate is required",
                'local_address_area' => 'Area is required',
                'local_address_building' => 'Building is required',
                'local_address_flat' => 'Flat is required',
                'local_address_floor' => 'Floor is required',
            ]);
        }else if($type === 'indian'){
            $validator = Validator::make($request->all(), [
                'permanent_address_line_1' => 'required|string',
            ],[
                'permanent_address_line_1' => 'Address line 1 is required'
            ]);
        }

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()); 
        }

        try{
            if($type === 'local'){
                $address = MemberLocalAddress::where('user_id', $user->id)->first();
                $address->update([
                    'governorate' => $input['governorate'],
                    'country' => isset($input['country']) ? $input['country'] : $address->country,
                    'region' => isset($input['region']) ? $input['region'] : $address->region,
                    'line_1' => $input['local_address_area'],
                    'building' => $input['local_address_building'],
                    'flat' => $input['local_address_flat'],
                    'floor' => $input['local_address_floor'],
                    'city' => isset($input['city']) ? $input['city'] : $address->city,
                    'zip' => isset($input['zip']) ? $input['zip'] : $address->zip,
                ]);
            }else if($type === 'indian'){
                $address = MemberPermanentAddress::where('user_id', $user->id)->first();
                $address->update([
                    'user_id' => $user->id,
                    'line_1' => $input['permanent_address_line_1'],
                    'line_2' => $input['permanent_address_line_2'],
                    'country' => isset($input['country']) ? $input['country'] : $address->country,
                    'region' => isset($input['region']) ? $input['region'] : $address->region,
                    'district' => isset($input['permanent_address_district']) ? $input['permanent_address_district'] : $address->district,
                    'city' => isset($input['city']) ? $input['city'] : $address->city,
                    'zip' => isset($input['zip']) ? $input['zip'] : $address->zip,
                    'contact' => isset($input['permanent_address_contact']) ? $input['permanent_address_country_code'].$input['permanent_address_contact'] : $address->contact,
                ]);
            }

            $member = Member::with(['localAddress', 'permanentAddress'])->where('user_id' , $user->id)->first();

            $data = [
                'success' => true,
                'local_address' => $member->localAddress,
                'permanent_address' => $member->permanent_address
            ];
            return $this->sendResponse($data, 'Address updated successfully.');
        }catch (\Exception $e) {
            return $this->sendError('Failed', $e);
        }

    }
}
