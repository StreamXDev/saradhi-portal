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
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Members\Exports\MembersListExport;
use Modules\Members\Models\Member;
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
        list($members, $filters) = $this->memberSearch();
        $members = $members->paginate();
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        if($request->get('export')){
            return $this->exportListToExcel($members);
        }
        return view('members::admin.member.list', compact('members', 'filters', 'units'));
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
        /*
        if (request()->get('unit') != null){
            $members->WhereHas('details', function($q) use ($input) {
                return $q->where('member_unit_id', request()->get('unit'));
            });
            $filters->put('unit', request()->get('unit'));
        }
            */

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
                    $member->relations[$key]->relatedDependent->avatar = $member->relations[$key]->relatedDependent->avatar ? url('storage/images/'. $member->relations[$key]->relatedDependent->avatar) : null;
                }
                
            }
        }
        $backTo = $prevPage ?  '/admin/members?page='.$prevPage : null;
        //dd($member);

        //Finding duplicate member with same civil id
        $duplicates = array();
        $duplicate_users = MemberDetail::select('user_id')->where('civil_id',$member->details->civil_id)->where('user_id', '!=', $member->user_id)->get();
        foreach($duplicate_users as $user){
            $duplicate_member = Member::with(['user', 'details', 'membership', 'localAddress', 'permanentAddress'])->where('user_id',$user->user_id)->first();
            array_push($duplicates, $duplicate_member);
        }
        return view('members::admin.member.show', compact('member', 'statuses', 'current_status', 'request_action', 'suggested_mid', 'countries', 'units', 'blood_groups', 'gender', 'district_kerala', 'backTo', 'duplicates'));
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
        $admin = Auth::user();
        $input = $request->all();
        $validator = Validator::make($request->all(), ...$this->validationRules($request));
        
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

    protected function validationRules($request)
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
            'civil_id' => 'required',
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
            $rules['spouse_civil_id'] = ['required', 'string'];
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
        dd($input);
    }
}
