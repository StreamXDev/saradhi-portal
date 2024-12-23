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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Members\Exports\MembersListExport;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDependent;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\MemberRelation;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;
use Modules\Members\Models\MemberTrustee;
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
        $menuParent = 'members';
        list($members, $filters) = $this->memberSearch();
        $members = $members->paginate();
        foreach($members as $member){
            $member->duplicate_civil_id = false;
            $duplicate = MemberDetail::select('user_id')->where('civil_id',$member->details->civil_id)->where('user_id', '!=', $member->user_id)->first();
            if($duplicate){
                $member->duplicate_civil_id = true;
            }
        }
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        if($request->get('export')){
            return $this->exportListToExcel($members);
        }
        return view('members::admin.member.list', compact('members', 'filters', 'units', 'menuParent'));
    }

    public function memberSearch()
    {

        $members = Member::with(['membership', 'details','user'])->where('active', 1)->orderBy(Membership::select('mid')->whereColumn('memberships.user_id', 'members.user_id'));

        $filters = collect(
            [
                'search_by' => '',
                'unit' => '',
                'status' => '',
            ]
        );

        if (request()->get('search_by') != null){
            $input = request()->get('search_by');
            $members->WhereHas('user', function($q) use ($input) {
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
                })
                ->orWhereHas('details', function($q) use ($input) {
                    return $q->where('civil_id', $input);
                });

            $filters->put('search_by', request()->get('search_by'));

        }
        if (request()->get('status') != null){
            $input = request()->get('status');
            $members->WhereHas('membership', function($q) use ($input) {
                return $q->where('status', $input);
            });
            $filters->put('status', request()->get('status'));
        }
        
        if (request()->get('unit') != null){
            $input = request()->get('unit');
            $members->WhereHas('details', function($q) use ($input) {
                return $q->where('member_unit_id', request()->get('unit'));
            });
            $filters->put('unit', request()->get('unit'));
        }
    

        //dd($members->toSql());

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
        $menuParent = 'members';
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
        $member['spouse'] = false;
        if($member->relations){
            foreach($member->relations as $key => $relative){
                if($relative->related_member_id){
                    if($relative->relatedMember->type == 'spouse'){
                        $member['spouse'] = $relative->related_member_id;
                    }
                    if($relative->relatedMember->active){
                        $member->relations[$key]->relatedMember->membership['idQr'] = QrCode::size(300)->generate(json_encode(['Name' =>  $member->relations[$key]->relatedMember->name,  'Membership ID' => $member->relations[$key]->relatedMember->membership->mid, 'Civil ID' => $member->relations[$key]->relatedMember->details->civil_id]));
                    }
                }else if($relative->related_dependent_id){
                    $member->relations[$key]->relatedDependent->avatar = $member->relations[$key]->relatedDependent->avatar ? url('storage/images/'. $member->relations[$key]->relatedDependent->avatar) : null;
                }
                
            }
        }
        $backTo = $prevPage ?  '/admin/members?page='.$prevPage : null;

        //Finding duplicate member with same civil id
        $duplicates = array();
        if($member->membership->joined_as == 'new'){
            $duplicate_users = MemberDetail::select('user_id')->where('civil_id',$member->details->civil_id)->where('user_id', '!=', $member->user_id)->get();
            foreach($duplicate_users as $user){
                $duplicate_member = Member::with(['user','membership', 'relations', 'relations.relatedMember.user', 'relations.relatedMember.membership'])->where('user_id',$user->user_id)->where('active',1)->first();
                if($duplicate_member){
                    array_push($duplicates, $duplicate_member);
                }
            }
        }
        return view('members::admin.member.show', compact('member', 'statuses', 'current_status', 'request_action', 'suggested_mid', 'countries', 'units', 'blood_groups', 'gender', 'district_kerala', 'duplicates', 'backTo',  'menuParent'));
    }

    /**
     * Generate member view pdf
     */
    public function exportViewToPDF($id)
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

    public function exportViewToExcel($id)
    {
        $member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress', 'relations', 'requests', 'committees', 'trustee', 'details.member_unit'])->where('user_id' , $id)->get();
        
        return Excel::download(new MemberExport($member), 'member.xlsx');
        
    }

    private function exportListToExcel($members)
    {
        list($members) = $this->memberSearch();
        $members = $members->get();
        return Excel::download(new MembersListExport($members), 'members.xlsx');
    }

    /**
     * Create Member
     */
    public function create()
    {
        $menuParent = 'members';
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
        return view('members::admin.member.create', compact('countries', 'units', 'blood_groups', 'district_kerala', 'suggested_mid', 'menuParent'));
    }

    /**
     * Store new member
     */
    public function store(Request $request)
    {
        $admin = Auth::user();
        $input = $request->all();
        $validator = Validator::make($request->all(), ...$this->newMemberValidation($request));
        
        if($input['type'] == 'family'){
            if($input['phone'] == $input['spouse_phone']){
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'spouse_phone' => ['Spouse phone and primary phone number should not be same'],
                 ]);
                throw $error; 
            }
        }

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', 'Some fields are not valid');       
        }

        DB::beginTransaction();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'phone' => $input['phone'],
            'calling_code' => $input['tel_country_code'],
            'email_verified_at' => now()
        ]);
        $user->assignRole(['Member']);
        $avatarName = 'av'.$user->id.'_'.time().'.'.$request->avatar->extension(); 
        $request->avatar->storeAs('public/images', $avatarName);
        User::where('id', $user->id)->update([
            'avatar' => $avatarName,
        ]);

        $new_member = Member::create([
            'user_id' => $user->id,
            'type' => 'primary',
            'name' => $input['name'],
            'gender' => $input['gender'],
            'blood_group' => $input['blood_group'],
            'active' => $input['verification']  == 'yes' ? 0 : 1
        ]);

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
        MemberDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'member_unit_id' => $input['member_unit_id'],
                'civil_id' => $input['civil_id'],
                'photo_civil_id_front' => $request->photo_civil_id_front ? $civil_id_front_name: null,
                'photo_civil_id_back' => $request->photo_civil_id_back ? $civil_id_back_name : null,
                'dob' => $input['dob'],
                'whatsapp' => $input['whatsapp'],
                'whatsapp_code' => $input['whatsapp_country_code'],
                'emergency_phone' => $input['emergency_phone'],
                'emergency_phone_code' => $input['emergency_country_code'],
                'company' => $input['company'],
                'profession' => $input['profession'],
                'company_address' => $input['company_address'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'photo_passport_front' => $request->photo_passport_front? $passport_front_name : null,
                'photo_passport_back' => $request->photo_passport_front ? $passport_back_name : null,
                'paci' => $input['paci'],
                'sndp_branch' => $input['sndp_branch'],
                'sndp_branch_number' => $input['sndp_branch_number'],
                'sndp_union' => $input['sndp_union'],
                'completed' => 1
            ]
        );

        Membership::create([
            'user_id' => $user->id,
            'type' => $input['type'],
            'introducer_name' => $input['introducer_name'],
            'introducer_phone' => $input['introducer_country_code'].$input['introducer_phone'],
            'introducer_mid' => $input['introducer_mid'],
            'introducer_unit' => $input['introducer_unit'],
            'mid' => $input['verification']  == 'yes' ? null : $input['primary_mid'],
            'start_date' => $input['verification']  == 'yes' ? null : $input['primary_start_date'],
            'updated_date' => $input['verification']  == 'yes' ? null : $input['primary_start_date'],
            'expiry_date' => $input['verification']  == 'yes' ? null : date('Y-m-d', strtotime('+1 year', strtotime($input['primary_start_date']))),
            'status' => $input['verification']  == 'yes' ? 'inactive' : 'active',
        ]);

        MemberLocalAddress::create([
            'user_id' => $user->id,
            'governorate' => $input['governorate'],
            'line_1' => $input['local_address_area'],
            'building' => $input['local_address_building'],
            'flat' => $input['local_address_flat'],
            'floor' => $input['local_address_floor'],
        ]);

        MemberPermanentAddress::create([
            'user_id' => $user->id,
            'line_1' => $input['permanent_address_line_1'],
            'district' => $input['permanent_address_district'],
            'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact'],
        ]);

        if($input['verification'] == 'yes'){
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
            MembershipRequest::create([
                'user_id' => $user->id,
                'request_status_id' => $status->id,
                'checked' => 1, 
                'updated_by' => $admin->id,
            ]);
            $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
            MembershipRequest::create([
                'user_id' => $user->id,
                'request_status_id' => $status->id,
                'updated_by' => $admin->id,
            ]);
        }

        if($input['type'] == 'family'){
            $spouse_user = User::create([
                'name' => $input['spouse_name'],
                'email' => $input['spouse_email'],
                'password' => $input['password'],
                'phone' => $input['spouse_phone'],
                'calling_code' => $input['spouse_tel_country_code'],
                'email_verified_at' => now()
            ]);
            $spouse_user->assignRole(['Member']);
            $spouseAvatarName = 'av'.$spouse_user->id.'_'.time().'.'.$request->spouse_avatar->extension(); 
            $request->spouse_avatar->storeAs('public/images', $spouseAvatarName);
            $input['avatar'] = $avatarName;
            User::where('id', $spouse_user->id)->update([
                'avatar' => $spouseAvatarName,
            ]);
            
            $new_member_spouse = Member::create([
                'user_id' => $spouse_user->id,
                'type' => 'spouse',
                'name' => $input['spouse_name'],
                'gender' => $input['spouse_gender'],
                'blood_group' => $input['spouse_blood_group'],
                'active' => $input['verification']  == 'yes' ? 0 : 1
            ]);

            if($request->spouse_photo_civil_id_front){
                $spouse_civil_id_front_name = 'cvf'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_civil_id_front->extension(); 
                $request->spouse_photo_civil_id_front->storeAs('public/images', $spouse_civil_id_front_name);
            }
            if($request->spouse_photo_civil_id_back){
                $spouse_civil_id_back_name = 'cvb'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_civil_id_back->extension(); 
                $request->spouse_photo_civil_id_back->storeAs('public/images', $spouse_civil_id_back_name);
            }
            if($request->spouse_photo_passport_front){
                $spouse_passport_front_name = 'ppf'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_passport_front->extension(); 
                $request->spouse_photo_passport_front->storeAs('public/images', $spouse_passport_front_name);
            }
            if($request->spouse_photo_passport_back){
                $spouse_passport_back_name = 'ppb'.$spouse_user->id.'_'.time().'.'.$request->spouse_photo_passport_back->extension(); 
                $request->spouse_photo_passport_back->storeAs('public/images', $spouse_passport_back_name);
            }
            MemberDetail::updateOrCreate(
                ['user_id' => $spouse_user->id],
                [
                    'member_unit_id' => $input['member_unit_id'],
                    'civil_id' => $input['spouse_civil_id'],
                    'photo_civil_id_front' => $request->spouse_photo_civil_id_front ? $spouse_civil_id_front_name : null,
                    'photo_civil_id_back' => $request->spouse_photo_civil_id_back ? $spouse_civil_id_back_name : null,
                    'dob' => $input['spouse_dob'],
                    'whatsapp' => $input['spouse_whatsapp'],
                    'whatsapp_code' => $input['spouse_whatsapp_country_code'],
                    'emergency_phone' => $input['spouse_emergency_phone'],
                    'emergency_phone_code' => $input['spouse_emergency_country_code'],
                    'company' => $input['spouse_company'],
                    'profession' => $input['spouse_profession'],
                    'company_address' => $input['spouse_company_address'],
                    'passport_no' => $input['spouse_passport_no'],
                    'passport_expiry' => $input['spouse_passport_expiry'],
                    'photo_passport_front' => $request->spouse_photo_passport_front ? $spouse_passport_front_name : null,
                    'photo_passport_back' => $request->spouse_photo_passport_front ? $spouse_passport_back_name : null,
                    'paci' => $input['spouse_paci'],
                    'sndp_branch' => $input['sndp_branch'],
                    'sndp_branch_number' => $input['sndp_branch_number'],
                    'sndp_union' => $input['sndp_union'],
                    'completed' => 1
                ]
            );

            Membership::create([
                'user_id' => $spouse_user->id,
                'type' => $input['type'],
                'introducer_name' => $input['introducer_name'],
                'introducer_phone' => $input['introducer_country_code'].$input['introducer_phone'],
                'introducer_mid' => $input['introducer_mid'],
                'introducer_unit' => $input['introducer_unit'],
                'mid' => $input['verification']  == 'yes' ? null : $input['spouse_mid'],
                'start_date' => $input['verification']  == 'yes' ? null : $input['spouse_start_date'],
                'updated_date' => $input['verification']  == 'yes' ? null : $input['spouse_start_date'],
                'expiry_date' => $input['verification']  == 'yes' ? null : date('Y-m-d', strtotime('+1 year', strtotime($input['spouse_start_date']))),
                'status' => $input['verification']  == 'yes' ? 'inactive' : 'active',
            ]);

            MemberLocalAddress::create([
                'user_id' => $spouse_user->id,
                'governorate' => $input['governorate'],
                'line_1' => $input['local_address_area'],
                'building' => $input['local_address_building'],
                'flat' => $input['local_address_flat'],
                'floor' => $input['local_address_floor'],
            ]);
    
            MemberPermanentAddress::create([
                'user_id' => $spouse_user->id,
                'line_1' => $input['permanent_address_line_1'],
                'district' => $input['permanent_address_district'],
                'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact'],
            ]);

            if($input['verification'] == 'yes'){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
                MembershipRequest::create([
                    'user_id' => $spouse_user->id,
                    'request_status_id' => $status->id,
                    'checked' => 1, 
                    'updated_by' => $admin->id,
                ]);
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                MembershipRequest::create([
                    'user_id' => $spouse_user->id,
                    'request_status_id' => $status->id,
                    'updated_by' => $admin->id,
                ]);
            }

            //Adding relationship
            $relation = MemberEnum::where('type', 'relationship')->where('slug', 'spouse')->first();
            $primaryMember = Member::where('user_id',$user->id)->first();
            MemberRelation::create([
                'member_id' => $primaryMember->id,
                'related_member_id' => $new_member_spouse->id,
                'relationship_id' => $relation->id,
            ]);
            MemberRelation::create([
                'member_id' => $new_member_spouse->id,
                'related_member_id' => $primaryMember->id,
                'relationship_id' => $relation->id,
            ]);

        }

        DB::commit();
        if($request->verification == 'yes'){
            return redirect('admin/members/requests');
        }
        return redirect('/admin/members');
    }

    protected function newMemberValidation($request)
    {
        $rules =  [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|numeric|unique:users,phone',
            'password' => ['required', Password::min(8)->numbers()->letters()->symbols()],
            'whatsapp' => 'required|numeric',
            'emergency_phone' => 'required|numeric',
            'dob' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'blood_group' => 'required',
            'civil_id' => 'required|digits:12',
            'passport_no' => 'required',
            'passport_expiry' => 'required',
            'type' => 'required',
            'governorate' => 'required',
            'member_unit_id' => 'required',
            'local_address_area' => 'required',
            'introducer_name' => 'required',
            'introducer_phone' => 'required',
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
        ];

        $messages = [
            'email.unique' => 'The Email ID is already used',
            'email.email' => 'Please enter a valid email ID',
            'phone.unique' => 'The phone number is already used',
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
            $rules['spouse_civil_id'] = ['required', 'digits:12'];
            $rules['spouse_passport_no'] = ['required', 'string'];
            $rules['spouse_passport_expiry'] = ['required', 'date_format:Y-m-d'];
            $rules['spouse_avatar'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'];
        }

        if($request->verification == 'no'){
            $rules['primary_mid'] = ['required', 'string'];
            $messages['primary_mid.required'] = 'Primary Member MID is required';
            if($request->type == 'family'){
                $rules['spouse_mid'] = ['required', 'string'];
                $messages['spouse_mid.required'] = 'Spouse MID is required';
            }
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
                'email' => 'required|email|unique:users,email,'.$input['user_id'],
                'phone' => 'required|numeric|unique:users,phone,'.$input['user_id'],
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
                'dob' => 'required|date_format:Y-m-d',
                'passport_no' => 'required',
                'passport_expiry' => 'required',
            ]);
        }elseif(isset($input['edit_membership'])){
            if(isset($input['current_type']) && $input['current_type'] == 'single'){
                $validator = Validator::make($request->all(), [
                    'user_id' => 'bail|required',
                    'mid'     => 'required',
                    'status'  => 'required',
                    'type'    => 'required'
                ]);
            }else{
                $validator = Validator::make($request->all(), [
                    'user_id' => 'bail|required',
                    'mid'     => 'required',
                ]);
            }
        }elseif(isset($input['edit_trustee'])){
            $validator = Validator::make($request->all(), [
                'tid' => 'required|unique:member_trustees,tid,'.$input['user_id'].',user_id',
                'title' => 'required|string',
                'joining_date' => 'required|date_format:Y-m-d',
                'status' => 'required'
            ]);
        }elseif(isset($input['edit_email'])){
            $validator = Validator::make($request->all(), [
                'user_id' => 'bail|required',
                'email' => 'required|email|unique:users,email,'.$input['user_id'],
            ]);
        }
 
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->with('error', 'Some fields are not valid');
        }
        
        $user_id = $input['user_id'];
        $user = User::where('id', $user_id)->first();

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
                'email' => $input['email'],
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

            if(isset($input['avatar'])){
                $existing_avatar = $user->avatar;
                if($existing_avatar){
                    Storage::delete('public/images/'.$existing_avatar);
                }
                $avatarName = 'av'.$user_id.'_'.time().'.'.$request->avatar->extension(); 
                $request->avatar->storeAs('public/images',$avatarName);
                User::where('id', $user_id)->update([
                    'avatar' => $avatarName,
                ]);
            }
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
        }elseif(isset($input['edit_membership'])){
            if(isset($input['current_type']) && $input['current_type'] == 'single'){
                $membershipUpdateData = [
                    'mid' => $input['mid'],
                    'type' => $input['type'],
                    'status' => $input['status'],
                ];
            }else{
                $membershipUpdateData = [
                    'mid' => $input['mid'],
                    'status' => $input['status'],
                ];
            }
            Membership::where('user_id', $user_id)->update($membershipUpdateData);
        }elseif(isset($input['edit_trustee'])){
            MemberTrustee::where('user_id',$user_id)->update([
                'tid' => $input['tid'],
                'title' => $input['title'],
                'joining_date' => $input['joining_date'],
                'status' => $input['status'],
                'active' => $input['status'] === 'active' ? 1 : 0
            ]);
        }elseif(isset($input['edit_email'])){
            User::where('id', $user_id)->update([
                'email' => $input['email'],
            ]);
            
            return redirect('admin/members')->with('success', 'Data updated successfully');
        }

        return redirect('admin/members/member/view/'.$user_id)->with('success', 'Data updated successfully');

    }

    /**
     * Merge duplicate members
     */
    public function merge(Request $request)
    {
        $input = $request->all();
        $new_user = $input['new_id'];
        $old_user = $input['old_id'];

        $nm_user = User::where('id', $new_user)->first();
        $nm = Member::with('relations')->where('user_id' , $new_user)->first();
        $nm_details = MemberDetail::where('user_id' , $new_user)->first();
        $nm_membership = Membership::where('user_id' , $new_user)->first();
        $nm_permanent_address = MemberPermanentAddress::where('user_id' , $new_user)->first();
        $nm_local_address = MemberLocalAddress::where('user_id' , $new_user)->first();
        $nm_requests = MembershipRequest::where('user_id', $new_user)->get();
        
        $om_user = User::where('id', $old_user)->first();
        $om = Member::with('relations')->where('user_id' , $old_user)->first();
        $om_details = MemberDetail::where('user_id' , $old_user)->first();
        $om_membership = Membership::where('user_id' , $old_user)->first();
        $om_local_address = MemberLocalAddress::where('user_id' , $old_user)->first();
        $om_permanent_address = MemberPermanentAddress::where('user_id' , $old_user)->first();

        DB::beginTransaction();

        if($nm->relations){
            foreach($nm->relations as $key => $relative){
                if($relative->related_member_id){
                    $related_member_id = $relative->related_member_id;
                    $relation_id = $relative->relationship_id;
                    MemberRelation::updateOrCreate([
                        'member_id' => $om->id,
                    ], [
                        'related_member_id' => $related_member_id,
                        'relationship_id' => $relation_id,
                    ]);
                    MemberRelation::updateOrCreate([
                        'related_member_id' => $om->id,
                    ], [
                        'member_id' => $related_member_id,
                        'relationship_id' => $relation_id,
                    ]);
                    $rm1 = MemberRelation::where('member_id', $nm->id)->where('related_member_id', $related_member_id)->first();
                    if($rm1){
                        $rm1->delete();
                    }
                    $rm2 = MemberRelation::where('member_Id', $related_member_id)->where('related_member_id', $nm->id)->first();
                    if($rm2){
                        $rm2->delete();
                    }
                    
                }
            }
        }

        MemberLocalAddress::updateOrCreate([
            'user_id' => $old_user
        ],[
            'governorate' => isset($input['governorate']) ? $nm_local_address->governorate : $om_local_address->governorate,
            'line_1' => isset($input['local_address_line_1']) ? $nm_local_address->line_1 : $om_local_address->line_1,
            'building' => isset($input['local_address_building']) ? $nm_local_address->building : $om_local_address->building,
            'flat' => isset($input['local_address_flat']) ? $nm_local_address->flat : $om_local_address->flat,
            'floor' => isset($input['local_address_floor']) ? $nm_local_address->floor : $om_local_address->floor,
        ]);
        $nm_local_address->delete();
        
        MemberPermanentAddress::updateOrCreate([
            'user_id' => $old_user
        ],[
            'line_1' =>  isset($input['permanent_address']) ? $nm_permanent_address->line_1 : $om_permanent_address->line_1,
            'line_2' =>  isset($input['permanent_address']) ? $nm_permanent_address->line_2 : $om_permanent_address->line_2,
            'district' =>  isset($input['permanent_address']) ? $nm_permanent_address->district : $om_permanent_address->district,
            'city' => isset($input['permanent_address']) ?  $nm_permanent_address->city : $om_permanent_address->city,
            'contact' =>  isset($input['permanent_address']) ? $nm_permanent_address->contact : $om_permanent_address->contact,
        ]);
        $nm_permanent_address->delete();

        $om_details->update([
            'whatsapp_code' => isset($input['whatsapp']) ? $nm_details->whatsapp_code : $om_details->whatsapp_code,
            'whatsapp' => isset($input['whatsapp']) ? $nm_details->whatsapp : $om_details->whatsapp,
            'emergency_phone_code' => isset($input['emergency_phone']) ? $nm_details->emergency_phone_code : $om_details->emergency_phone_code,
            'emergency_phone' => isset($input['emergency_phone']) ? $nm_details->emergency_phone : $om_details->emergency_phone,
            'dob' => isset($input['dob']) ? $nm_details->dob : $om_details->dob,
            'passport_no' =>  isset($input['passport_no']) ? $nm_details->passport_no : $om_details->passport_no,
            'passport_expiry' =>  isset($input['passport_expiry']) ? $nm_details->passport_expiry : $om_details->passport_expiry,
            'company' =>  isset($input['company']) ? $nm_details->company : $om_details->company,
            'profession' =>  isset($input['profession']) ? $nm_details->profession : $om_details->profession,
            'company_address' =>  isset($input['company_address']) ? $nm_details->company_address : $om_details->company_address,
            'paci' =>  isset($input['paci']) ? $nm_details->paci : $om_details->paci,
            'sndp_branch' =>  isset($input['sndp_branch']) ? $nm_details->sndp_branch : $om_details->sndp_branch,
            'sndp_branch_number' =>  isset($input['sndp_branch_number']) ? $nm_details->sndp_branch_number : $om_details->sndp_branch_number,
            'sndp_union' => isset($input['sndp_union']) ?  $nm_details->sndp_union : $om_details->sndp_union,
            'member_unit_id' =>  isset($input['unit']) ? $nm_details->member_unit_id : $om_details->member_unit_id,
        ]);
        $nm_details->delete();
        
        $om->update([
            'name' => isset($input['name']) ? $nm_user->name : $om_user->name,
            'blood_group' => isset($input['blood_group']) ? $nm->blood_group : $om->blood_group,
        ]);
        $nm->delete();

        $om_membership->update([
            'type' => $nm_membership->type
        ]);
        $nm_membership->delete();

        foreach($nm_requests as $rq){
            $rq->forceDelete();
        }

        $nm_user->delete();
        
        $om_user->update([
            'avatar' => isset($input['avatar']) ? $nm_user->avatar : $om_user->avatar,
            'email' => isset($input['email']) ? $nm_user->email : $om_user->email,
            'name' => isset($input['name']) ? $nm_user->name : $om_user->name,
            'password' => $nm_user->password,
            'phone' => isset($input['phone']) ? $nm_user->phone : $om_user->phone,
            'calling_code' => isset($input['phone']) ? $nm_user->calling_code : $om_user->calling_code,
            'email_verified_at' => $nm_user->email_verified_at,
        ]);
        DB::commit();

        return redirect('admin/members/member/view/'.$old_user)->with('success', 'Member merged successfully');
        
    }

    /**
     * Create family member form
     * 
     */
    public function createFamilyMember($id){
        $parent = Member::with(['details', 'membership', 'relations'])->where('user_id', $id)->first();
        if($parent->type !== 'primary'){
            // parent is not a primary member
            return Redirect::back()->with('error', 'Parent should be a primary member');
        }
        $parent->hasSpouse = false;
        if($parent->relations){
            foreach($parent->relations as $relation){
                if($relation->related_member_id){
                    $parent->hasSpouse = true;
                }
            }
        }
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
        return view('members::admin.member.create_family', compact('countries', 'units', 'blood_groups', 'district_kerala', 'suggested_mid', 'parent'));
    }

    /**
     * Store family member
     */
    public function storeFamilyMember(Request $request)
    {
        $admin = Auth::user();
        $input = $request->all();
        $validator = Validator::make($request->all(), ...$this->familyMemberValidation($request));

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', 'Some fields are not valid');       
        }

        $parent = Member::with(['details', 'membership', 'relations'])->where('user_id', $input['parent'])->first();

        if($input['profile_type'] == 'spouse'){
            $parent->hasSpouse = false;
            if($parent->relations){
                foreach($parent->relations as $relation){
                    if($relation->related_member_id){
                        $parent->hasSpouse = true;
                    }
                }
            }
            if($parent->hasSpouse){
                // Member already has a spouse
                return Redirect::back()->with('error', 'Member already has a spouse');
            }


            DB::beginTransaction();

            Membership::where('user_id', $input['parent'])->update([
                'type' => 'family'
            ]);

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($this->rand_passwd()),
                'phone' => $input['phone'],
                'calling_code' => $input['tel_country_code'],
                'email_verified_at' => now()
            ]);
            $user->assignRole(['Member']);
            $avatarName = 'av'.$user->id.'_'.time().'.'.$request->avatar->extension(); 
            $request->avatar->storeAs('public/images', $avatarName);
            User::where('id', $user->id)->update([
                'avatar' => $avatarName,
            ]);
    
            $new_member = Member::create([
                'user_id' => $user->id,
                'type' => 'spouse',
                'name' => $input['name'],
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group'],
                'active' => $input['verification']  == 'yes' ? 0 : 1
            ]);

            MemberDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'member_unit_id' => $input['member_unit_id'],
                    'civil_id' => $input['civil_id'],
                    'dob' => $input['dob'],
                    'whatsapp' => $input['whatsapp'],
                    'whatsapp_code' => $input['whatsapp_country_code'],
                    'emergency_phone' => $input['emergency_phone'],
                    'emergency_phone_code' => $input['emergency_country_code'],
                    'company' => $input['company'],
                    'profession' => $input['profession'],
                    'company_address' => $input['company_address'],
                    'passport_no' => $input['passport_no'],
                    'passport_expiry' => $input['passport_expiry'],
                    'paci' => $input['paci'],
                    'sndp_branch' => $input['sndp_branch'],
                    'sndp_branch_number' => $input['sndp_branch_number'],
                    'sndp_union' => $input['sndp_union'],
                    'completed' => 1
                ]
            );
    
            Membership::create([
                'user_id' => $user->id,
                'type' => 'family',
                'mid' => $input['verification']  == 'yes' ? null : $input['mid'],
                'start_date' => $input['verification']  == 'yes' ? null : $input['start_date'],
                'updated_date' => $input['verification']  == 'yes' ? null : $input['start_date'],
                'expiry_date' => $input['verification']  == 'yes' ? null : date('Y-m-d', strtotime('+1 year', strtotime($input['start_date']))),
                'status' => $input['verification']  == 'yes' ? 'inactive' : 'active',
            ]);
    
            MemberLocalAddress::create([
                'user_id' => $user->id,
                'governorate' => $input['governorate'],
                'line_1' => $input['local_address_area'],
                'building' => $input['local_address_building'],
                'flat' => $input['local_address_flat'],
                'floor' => $input['local_address_floor'],
            ]);
    
            MemberPermanentAddress::create([
                'user_id' => $user->id,
                'line_1' => $input['permanent_address_line_1'],
                'district' => $input['permanent_address_district'],
                'contact' => $input['permanent_address_country_code'].$input['permanent_address_contact'],
            ]);

            if($input['verification'] == 'yes'){
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'saved')->first();
                MembershipRequest::create([
                    'user_id' => $user->id,
                    'request_status_id' => $status->id,
                    'checked' => 1, 
                    'updated_by' => $admin->id,
                ]);
                $status = MemberEnum::where('type', 'request_status')->where('slug', 'submitted')->first();
                MembershipRequest::create([
                    'user_id' => $user->id,
                    'request_status_id' => $status->id,
                    'updated_by' => $admin->id,
                ]);
            }

            //Adding relationship
            $relation = MemberEnum::where('type', 'relationship')->where('slug', 'spouse')->first();
            $primaryMember = Member::where('user_id',$parent->user_id)->first();
            MemberRelation::create([
                'member_id' => $primaryMember->id,
                'related_member_id' => $new_member->id,
                'relationship_id' => $relation->id,
            ]);
            MemberRelation::create([
                'member_id' => $new_member->id,
                'related_member_id' => $primaryMember->id,
                'relationship_id' => $relation->id,
            ]);

            DB::commit();
            if($request->verification == 'yes'){
                return redirect('admin/members/requests');
            }
        }else if($input['profile_type'] == 'child'){

            $childInput = [
                'name' => $input['name'],
                'email' => isset($input['email']) ? $input['email'] : null,
                'calling_code' => isset($input['calling_code']) ? $input['calling_code'] : null,
                'phone' => isset($input['phone']) ? $input['phone'] : null,
                'gender' => $input['gender'],
                'blood_group' => $input['blood_group'],
                'civil_id' => $input['civil_id'],
                'dob' => $input['dob'],
                'passport_no' => $input['passport_no'],
                'passport_expiry' => $input['passport_expiry'],
                'parent_user_id' => $parent->user_id,
                'parent_mid' => $parent->membership->mid,
                'type' => 'child'
            ];
            DB::beginTransaction();
            Membership::where('user_id', $input['parent'])->update([
                'type' => 'family'
            ]);
            $child = MemberDependent::create($childInput);
            $child_avatar = 'cvf'.$child->id.'_'.time().'.'.$request->avatar->extension(); 
            $request->avatar->storeAs('public/images', $child_avatar);
            MemberDependent::where('id', $child->id)->update([
                'avatar' => $child_avatar,
            ]);

            $relations_against_primary_member = MemberRelation::where('member_id', $parent->id)->get();
            $parent_primary = $parent->id;
            $parent_spouse = null;
            $siblings = [];
            foreach($relations_against_primary_member as $primary_relations){
                if($primary_relations->related_member_id !== null){
                    $rm = Member::where('id',$primary_relations->related_member_id)->first();
                    if($rm->type === 'primary'){
                        $parent_primary = $rm->id;
                    }else{
                        $parent_spouse = $rm->id;
                    }
                }else if($primary_relations->related_dependent_id !== null){
                    $rd = MemberDependent::where('id', $primary_relations->related_dependent_id)->first();
                    $siblings[] = $rd->id;
                }
            }
            $parent_relation_type = MemberEnum::where('type', 'relationship')->where('slug', 'parent')->first();
            $child_relation_type = MemberEnum::where('type', 'relationship')->where('slug', 'child')->first();
            $sibling_relation_type = MemberEnum::where('type', 'relationship')->where('slug', 'sibling')->first();
            if($parent_primary){
                MemberRelation::create([
                    'member_id' => $parent_primary,
                    'related_dependent_id' => $child->id,
                    'relationship_id' => $parent_relation_type->id,
                ]);
                MemberRelation::create([
                    'related_member_id' => $parent_primary,
                    'dependent_id' => $child->id,
                    'relationship_id' => $child_relation_type->id,
                ]);
            }
            if($parent_spouse){
                MemberRelation::create([
                    'member_id' => $parent_spouse,
                    'related_dependent_id' => $child->id,
                    'relationship_id' => $parent_relation_type->id,
                ]);
                MemberRelation::create([
                    'related_member_id' => $parent_spouse,
                    'dependent_id' => $child->id,
                    'relationship_id' => $child_relation_type->id,
                ]);
            }
            if($siblings){
                foreach($siblings as $sibling){
                    MemberRelation::create([
                        'dependent_id' => $sibling,
                        'related_dependent_id' => $child->id,
                        'relationship_id' => $sibling_relation_type->id,
                    ]);
                    MemberRelation::create([
                        'related_dependent_id' => $sibling,
                        'dependent_id' => $child->id,
                        'relationship_id' => $sibling_relation_type->id,
                    ]);
                }
            }
            DB::commit();
        }
        
        return redirect('/admin/members/member/view/'.$parent->user_id);

    }

    protected function rand_passwd( $length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
        return substr( str_shuffle( $chars ), 0, $length );
    }

    protected function familyMemberValidation($request)
    {
        $rules =  [
            'name' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'blood_group' => 'required',
            'civil_id' => 'required|digits:12',
            'passport_no' => 'required',
            'passport_expiry' => 'required',
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg','max:2048'],
        ];

        $messages = [
            'avatar.required' => 'Profile photo is required',
            'avatar.image' => 'Profile photo should be an image',
            'avatar.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, gif, svg.',
            'avatar.max' => 'Profile photo size should not be exceeded more than 2mb',
        ];

        if($request->profile_type == 'spouse'){
            $rules['name'] = ['required', 'string'];
            $rules['email'] = ['required', Rule::unique(User::class, 'email')];
            $rules['phone'] = ['required', Rule::unique(User::class, 'phone')];
            $rules['whatsapp'] = ['required', 'numeric'];
            $rules['emergency_phone'] = ['required', 'numeric'];
            $rules['governorate'] = ['required'];
            $rules['member_unit_id'] = ['required'];
            $rules['local_address_area'] = ['required'];

            $messages['email.required'] = 'Please enter a valid email ID';
            $messages['email.unique'] = 'The email id already used';
            $messages['phone.required'] = 'Enter a valid phone number';
            $messages['phone.unique'] = 'The phone number is already used';
            $messages['whatsapp.required'] = 'Whatsapp number is required';
            $messages['whatsapp.numeric'] = 'Enter a valid whatsapp number';
            $messages['emergency_phone.required'] = 'Emergency phone number is required';
            $messages['emergency_phone.numeric'] = 'Enter a valid phone number';
            $messages['governorate.required'] = 'Governorate is required';
            $messages['member_unit_id.required'] = 'Unit is required';
            $messages['local_address_area.required'] = 'Line 1 is required';

        }

        return [
            $rules,
            $messages
        ];
    }

    public function deleteFamilyMember(Request $request)
    {

        $admin = Auth::user();
        $validator = Validator::make($request->all(), ['dependent_id' => 'required', 'primary_member' => 'required']);
        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput()->with('error', 'Some fields are not valid');       
        }
        $input = $request->all();
        $dependent = MemberDependent::findOrFail($input['dependent_id']);
        MemberRelation::where('dependent_id', $input['dependent_id'])->delete();
        MemberRelation::where('related_dependent_id', $input['dependent_id'])->delete();

        $existing_thumb = $dependent->avatar;
        if($existing_thumb){
            Storage::delete('public/images/'.$existing_thumb);
            
        }
        $dependent->delete();

        return redirect('/admin/members/member/view/'.$input['primary_member']);
    }
}
