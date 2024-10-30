<?php

namespace Modules\Members\Http\Controllers\Admin;

use Modules\Members\Exports\MemberExport;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
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
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:user.create', ['only' => ['create','store']]);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        

        list($members, $filters) = $this->memberSearch();
        $members = $members->paginate();
        return view('members::admin.member.list', compact('members', 'filters'));
    }

    public function memberSearch()
    {

        $members = Member::with(['membership', 'details','user'])->where('active', 1)->orderBy('id','desc');
        $filters = collect(
            [
                'search_by' => '',
                'unit' => '',
                'mid' => '',
                'status' => '',
            ]
        );

        if (request()->get('search_by') != null){
            $input = request()->get('search_by');
            $members->where('type', 'LIKE', '%' .$input. '%')
                ->orWhereHas('user', function($q) use ($input) {
                    return $q->where('name', 'LIKE', '%' . $input . '%');
                })
                ->orWhereHas('user', function($q) use ($input) {
                    return $q->where('email', $input);
                })
                ->orWhereHas('user', function($q) use ($input) {
                    return $q->where('phone', $input);
                })
                ->orWhereHas('membership', function($q) use ($input) {
                    return $q->where('mid', $input);
                });

            $filters->put('search_by', request()->get('search_by'));
        }

        return [
            $members,
            $filters
        ];
    }

    /**
     * Show the specified resource.
     */
    public function show($id, $prevPage = null)
    {
        
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'relations.relatedMember.user', 'relations.relatedMember.membership', 'relations.relatedMember.details', 'relations.relatedDependent', 'requests', 'committees', 'trustee'])->where('user_id' , $id)->first();
        //dd($member);
        $statuses = requestStatusDisplay($id);
        $current_status = MembershipRequest::where('user_id', $id)->latest('id')->first();
        $request_action = requestByPermission($current_status);
        //dd($statuses);
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

        //Member ID
        if($member->membership){
            $member->membership['idQr'] = QrCode::size(300)->generate(json_encode(['Name' =>  $member->name,  'Membership ID' => $member->membership->mid, 'Civil ID' => $member->details->civil_id]));
        }
        if($member->relations){
            foreach($member->relations as $key => $relative){
                if($relative->related_member_id){
                    if($relative->relatedMember->active){
                        $member->relations[$key]->relatedMember->membership['idQr'] = QrCode::size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedMember->name,  'Membership ID' => $member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $member->relations[$key]->relatedMember->details->civil_id]));
                    }
                }else if($relative->related_dependent_id){
                    $member->relations[$key]->relatedDependent->avatar = $member->relations[$key]->relatedDependent->avatar && url('storage/images/'. $member->relations[$key]->relatedDependent->avatar);
                }
                
            }
        }
        $backTo = $prevPage ?  '/admin/members?page='.$prevPage : null;
        //dd($member);
        
        return view('members::admin.member.show', compact('member', 'statuses', 'current_status', 'request_action', 'suggested_mid', 'countries', 'units', 'blood_groups', 'gender', 'district_kerala', 'backTo'));
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
     * Create Member
     */
    public function create()
    {
        $countries = Country::with('regions')->where('active', 1)->get();
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $suggested_mid = Membership::max('mid') + 1;
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
        return view('members::admin.member.create', compact('countries', 'units', 'blood_groups', 'district_kerala', 'suggested_mid'));
    }

    /**
     * Store new member
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();

        $validator = Validator::make($request->all(), ...$this->validationRules($request));
        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }

        $avatarName = 'av'.$user->id.'_'.time().'.'.$request->avatar->extension(); 
        $request->avatar->storeAs('public/images', $avatarName);

        if($request->photo_civil_id_front){
            $civil_id_front_name = 'cvf'.$user->id.'_'.time().'.'.$request->photo_civil_id_front->extension(); 
            $request->photo_civil_id_front->storeAs('public/images', $civil_id_front_name);
        }

        if($request->photo_civil_id_back){
            $civil_id_back_name = 'cvb'.$user->id.'_'.time().'.'.$request->photo_civil_id_back->extension(); 
            $request->photo_civil_id_back->storeAs('public/images', $civil_id_back_name);
        }

        if($request->photo_passport_front){
            $passport_front_name = 'ppf'.$user->id.'_'.time().'.'.$request->photo_passport_front->extension(); 
            $request->photo_passport_front->storeAs('public/images', $passport_front_name);
        }

        if($request->photo_passport_back){
            $passport_back_name = 'ppb'.$user->id.'_'.time().'.'.$request->photo_passport_back->extension(); 
            $request->photo_passport_back->storeAs('public/images', $passport_back_name);
        }

        $input['avatar'] = $avatarName;

        DB::beginTransaction();

        User::create([

        ]);

        DB::commit();

    }

    protected function validationRules($request)
    {
        $rules =  [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|email|unique:users,phone',
            'password' => 'required',
            'whatsapp' => 'required',
            'emergency_phone' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'blood_group' => 'required',
            'civil_id' => 'required',
            'passport_no' => 'required',
            'passport_expiry' => 'required',
            'type' => 'required',
            'governorate' => 'required',
            'local_address_area' => 'required',
            'local_address_building' => 'required',
            'local_address_flat' => 'required',
            'local_address_floor' => 'required',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $messages = [
            'name.required'    => 'Name is required',
            'email.required'    => 'Email is required',
            'phone.required'    => 'Phone is required',
            'password.required'    => 'Password is required',
            'whatsapp.required'    => 'Whatsapp is required',
            'emergency_phone.required'    => 'Emergency is required',
            'dob.required'    => 'Date of birth is required',
            'gender.required'    => 'Gender is required',
            'blood_group.required'    => 'Blood Group is required',
            'civil_id.required'    => 'Civil ID is required',
            'passport_no.required'    => 'Passport No. is required',
            'passport_expiry.required'    => 'Passport Expiry is required',
            'type.required'    => 'Membership Type is required',
            'governorate.required'    => 'Governorate is required',
            'local_address_area.required'    => 'Kuwait address area is required',
            'local_address_building.required'    => 'Kuwait address building is required',
            'local_address_flat.required'    => 'Kuwait address flat is required',
            'local_address_floor.required'    => 'Kuwait address floor is required',

            'avatar.required' => 'Profile photo is required',
            'avatar.image' => 'Profile photo should be an image',
            'avatar.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif, svg.',
            'avatar.max' => 'Profile photo size should not be exceeded more than 2mb',
        ];

        if($request->type == 'family'){
            $rules['spouse_name'] = ['required', 'string'];
            $rules['spouse_email'] = ['required', Rule::unique(User::class, 'email')];
            $rules['spouse_phone'] = ['required', Rule::unique(User::class, 'phone')];
            $rules['spouse_whatsapp'] = ['required', 'numeric'];
            $rules['spouse_emergency_phone'] = ['required', 'numeric'];
            $rules['spouse_dob'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_gender'] = ['required', 'string'];
            $rules['spouse_blood_group'] = ['required', 'string'];
            $rules['spouse_civil_id'] = ['required', 'string'];
            $rules['spouse_passport_no'] = ['required', 'string'];
            $rules['spouse_passport_expiry'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_avatar'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];

            $messages['spouse_phone.required'] = 'Spouse Phone number is required';
            $messages['spouse_phone.unique'] = 'Spouse This phone number is already used';
            $messages['spouse_whatsapp.required'] = 'Spouse Whatsapp Number is required';
            $messages['spouse_whatsapp.numeric'] = 'Spouse Whatsapp number should be a number';
            $messages['spouse_emergency_phone.required'] = 'Spouse Emergency Phone Number is required';
            $messages['spouse_emergency_phone.numeric'] = 'Spouse Emergency Phone number should be a number';
            $messages['spouse_civil_id.required'] = 'Spouse Civil ID is required';
            $messages['spouse_civil_id.string'] = 'Spouse Civil ID is not valid';
            $messages['spouse_dob.required'] = 'Spouse Date of birth is required';
            $messages['spouse_dob.date_format'] = 'Spouse Date of birth should be of format Y-m-d';
            $messages['spouse_passport_no.required'] = 'Spouse Passport number is required';
            $messages['spouse_passport_expiry.required'] = 'Spouse Passport expiry date is required';
            $messages['spouse_passport_expiry.date_format'] = 'Spouse Passport expiry date should be of format Y-m-d';
            $messages['spouse_gender.required'] = 'Spouse Gender is required';
            $messages['spouse_blood_group.required'] = 'Spouse Blood group is required';

            $messages['spouse_avatar.required'] = 'Spouse Profile photo is required';
            $messages['spouse_avatar.image'] = 'Spouse Profile photo should be an image';
            $messages['spouse_avatar.mimes'] = 'Spouse Profile photo must be a file of type: jpeg, png, jpg, gif, svg.';
            $messages['spouse_avatar.max'] = 'Spouse Profile photo size should not be exceeded more than 2mb';
        }

        return [
            $rules,
            $messages
        ];
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
