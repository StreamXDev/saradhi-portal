<?php

namespace Modules\Members\Http\Controllers\Admin;

use Modules\Members\Exports\MemberExport;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Models\MemberUnit;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = Member::with(['details', 'user'])->where('active', 1)->paginate(20);
        $sortedResult = $members->getCollection()->sortByDesc('id')->values();
        $members->setCollection($sortedResult);
        //dd($members);
        return view('members::admin.member.list', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('members::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedTo.user', 'requests', 'committees', 'trustee'])->where('user_id' , $id)->first();
        $statuses = requestStatusDisplay($id);
        $current_status = MembershipRequest::where('user_id', $id)->latest('id')->first();
        $request_action = requestByPermission($current_status);
        //dd($request_action);
        $suggested_mid = Membership::max('mid') + 1;

        $countries = Country::with('regions')->where('active', 1)->get();
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $gender = [
            ['name'=>'Male', 'slug' => 'male'], 
            ['name' => 'Female', 'slug' => 'female']
        ];
        $district_kerala = array(
            ['name' => 'Alappuzha', 'slug' => 'alappuzha'],
            ['name' => 'Ernakulam', 'slug' => 'ernakulam'],
            ['name' => 'Idukki', 'slug' => 'idukki'],
            ['name' => 'Kannur', 'slug' => 'kannur'],
            ['name' => 'Kasaragod', 'slug' => 'kasaragod'],
            ['name' => 'Kollam', 'slug' => 'kollam'],
            ['name' => 'Kottayam', 'slug' => 'kottayam'],
            ['name' => 'Kozhikkode', 'slug' => 'kozhikkode'],
            ['name' => 'Malappuram', 'slug' => 'malappuram'],
            ['name' => 'Palakkad', 'slug' => 'palakkad'],
            ['name' => 'Pathanamthitta', 'slug' => 'pathanamthitta'],
            ['name' => 'Thiruvananthapuram', 'slug' => 'thriuvananthapuram'],
            ['name' => 'Thrissur', 'slug' => 'thrissur'],
            ['name' => 'Wayanada', 'slug' => 'wayanad'],
            ['name' => 'Other', 'slug' => 'other'],
        );

        $idQr = QrCode::size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
        //dd($member);
        return view('members::admin.member.show', compact('member', 'statuses', 'current_status', 'request_action', 'suggested_mid', 'countries', 'units', 'blood_groups', 'gender', 'district_kerala', 'idQr'));
    }

    /**
     * Generate member view pdf
     */
    public function generatePDF($id)
    {
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'requests', 'committees', 'trustee'])->where('user_id' , $id)->first();
        
        $data = [
            'title' => 'Membership Application',
            'date' => date('M d, Y'),
            'member' => $member
        ];

        //return view('members::admin.member.pdf', compact('data'));
        $pdf = Pdf::loadView('members::admin.member.pdf', compact('data'));

        return $pdf->download('member_request_'.str_replace(" ", "-", $member->user->name).'.pdf');

    }

    public function generateExcel($id)
    {
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'requests', 'committees', 'trustee', 'details.member_unit'])->where('user_id' , $id)->get();
        
        return Excel::download(new MemberExport($member), 'member.xlsx');
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('members::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        
        $input = $request->all();

        if(isset($input['edit_address'])){
            $validator = Validator::make($request->all(), [
                'user_id' => 'bail|required',
                'governorate' => 'required',
                'local_address_area' => 'required',
                'local_address_building' => 'required',
            ]);
        }elseif(isset($input['edit_basic'])){
            $validator = Validator::make($request->all(), [
                'user_id' => 'bail|required',
                'name' => 'required',
                'tel_country_code' => 'required|numeric',
                'phone' => 'required|numeric',
                'whatsapp_country_code' => 'required|numeric',
                'whatsapp' => 'required|numeric',
                'emergency_country_code' => 'required|numeric',
                'emergency_phone' => 'required|numeric',
                'member_unit_id' => 'required|numeric',
                'civil_id' => 'required',
            ]);
        }elseif(isset($input['edit_personal'])){
            $validator = Validator::make($request->all(), [
                'user_id' => 'bail|required',
                'gender' => 'required',
                'blood_group' => 'required',
                'dob' => 'required',
                'passport_no' => 'required',
                'passport_expiry' => 'required',
            ]);
        }
 
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }
        
        $user_id = $input['user_id'];

        if(isset($input['edit_address'])){
            MemberLocalAddress::where('user_id', $user_id)->update([
                'governorate' => $input['governorate'],
                'line_1' => $input['local_address_area'],
                'building' => $input['local_address_building'],
                'flat' => $input['local_address_flat'],
                'floor' => $input['local_address_floor'],
            ]);

            MemberPermanentAddress::where('user_id', $user_id)->update([
                'line_1' => $input['permanent_address_line_1'],
                'district' => $input['permanent_address_district'],
                'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact']
            ]);
        }elseif(isset($input['edit_basic'])){
            
            User::where('id', $user_id)->update([
                'name' => $input['name'],
                'phone' => $input['phone'],
                'calling_code' => $input['tel_country_code']
            ]);
            Member::where('user_id', $user_id)->update([
                'name' => $input['name'],
            ]);
            MemberDetail::where('user_id', $user_id)->update([
                'whatsapp' => $input['whatsapp'],
                'whatsapp_code' => $input['whatsapp_country_code'],
                'emergency_phone' => $input['emergency_phone'],
                'emergency_phone_code' => $input['emergency_country_code'],
                'civil_id' => $input['civil_id'],
                'member_unit_id' => $input['member_unit_id'],
                'paci' => $input['paci'],
            ]);

            if(isset($input['photo_civil_id_front'])){
                $civil_id_front_name = 'cvf'.$user_id.'_'.time().'.'.$request->photo_civil_id_front->extension(); 
                $request->photo_civil_id_front->storeAs('public/images', $civil_id_front_name);
                MemberDetail::where('user_id', $user_id)->update([
                    'photo_civil_id_front' => $civil_id_front_name,
                ]);
            }
            if(isset($input['photo_civil_id_back'])){
                $civil_id_back_name = 'cvb'.$user_id.'_'.time().'.'.$request->photo_civil_id_back->extension(); 
                $request->photo_civil_id_back->storeAs('public/images', $civil_id_back_name);
                MemberDetail::where('user_id', $user_id)->update([
                    'photo_civil_id_back' => $civil_id_back_name,
                ]);
            }
        }elseif(isset($input['edit_personal'])){
            Member::where('user_id', $user_id)->update([
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group'],
            ]);
            MemberDetail::where('user_id', $user_id)->update([
                'dob' => $input['dob'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'profession' => $input['profession'],
                'company' => $input['company'],
                'company_address' => $input['company_address'],
            ]);
            if(isset($input['photo_passport_front'])){
                $passport_front_name = 'ppf'.$user_id.'_'.time().'.'.$request->photo_passport_front->extension(); 
                $request->photo_passport_front->storeAs('public/images', $passport_front_name);
                MemberDetail::where('user_id', $user_id)->update([
                    'photo_passport_front' => $passport_front_name,
                ]);
            }
            if(isset($input['photo_passport_back'])){
                $passport_back_name = 'ppb'.$user_id.'_'.time().'.'.$request->photo_passport_back->extension(); 
                $request->photo_passport_back->storeAs('public/images', $passport_back_name);
                MemberDetail::where('user_id', $user_id)->update([
                    'photo_passport_back' => $passport_back_name,
                ]);
            }
        }

        return redirect('admin/members/member/view/'.$user_id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
