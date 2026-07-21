<?php

namespace Modules\Imports\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\Members\Repositories\MemberRepository;
use Modules\Members\Repositories\MembershipRequestRepository;
use Modules\Members\Repositories\MemberUnitRepository;

class ReceiveMemberService
{
    
    private $api;
    private $accessToken;
    private $headers;
    private $thisUser;

    public function __construct(
        protected MemberRepository $memberRepository,
        protected MemberUnitRepository $unitRepository,
        protected MembershipRequestRepository $requestRepository
    ){
        $this->api = env('NEW_PORTAL_API').'api/';
        // Generating access token
        if(!session()->has('transfer_token')){
            $this->login();
        }
        $this->accessToken = Session::get('transfer_token');
        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
        $this->thisUser = Auth::user();
    }


    /**
     * Login to new portal
     */
    private function login(){
        $response = Http::post($this->api.'auth/login', [
            'email' => 'shanoob.sekhar@gmail.com',
            'password' => 'abc@123',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            session(['transfer_token' => $data['data']['token']]);
        }
    }


    // Fetching Member Details from old portal
    public function createMember(array $data)
    {
        try {
            $newUserId = $data['user_id'];
            $newUserDetails = $this->getUserDetails($newUserId);
            if(!$newUserDetails){
                return; 
            }
            $ret = $this->getMemberDetails($newUserId);
            if(!$ret){
                return;
            }
            DB::beginTransaction();
            $user = User::where('email', $newUserDetails->email)->first();
            $user->email_verified_at = $newUserDetails->email_verified_at;
            $user->avatar = $this->saveFileFromUrl($ret->user_id, $ret->user->avatar, null, 'av');
            $user->save();
    
            $memberUnit = $this->unitRepository->findBySlug($ret->unit->code);
            $introducerUnit = $this->unitRepository->findBySlug($ret->introducer_unit->code);

            $memberData = [
                'type' => $ret->user->details->gender == 'male' ? 'primary' : 'spouse',
                'name' => $ret->user->name,
                'mid' => $ret->mid,
                'gender' => $ret->user->details->gender,
                'blood_group' => $ret->user->details->blood_group,
                'active' => $ret->status == 'active' ? 1 : 0,
            ];
            $this->memberRepository->updateOrCreateMember($user->id, $memberData);
    
            
            $membershipData = [
                'mid' => $ret->mid,
                'start_date' => $ret->join_date,
                'expiry_date' => $ret->subscription->end_date, 
                'type' => $ret->family_in_kuwait ? 'family' : 'single', 
                'family_in' => $ret->family_in_kuwait ? 'kuwait': 'india',
                'status' => $ret->status,
                'joined_as' => 'new',
                'introducer_name' => $ret->introducer_name,
                'introducer_phone' => $ret->introducer_phone,
                'introducer_phone_code' => $ret->introducer_calling_code,
                'introducer_mid' => $ret->introducer_mid,
                'introducer_unit' => $introducerUnit->id,
            ];
            $this->memberRepository->updateOrCreateMembership($user->id, $membershipData);
    
            $memRequests[] = [
                'user_id' => $user->id,
                'request_status_id' => 2,
                'checked' => 1,
                'updated_by' => $user->id,
            ];
            $requests = $ret->request_history;
            foreach($requests as $req){
                $updated_by = User::where('email', $req->updated_by->email)->fist();
                if($req->stage->slug !== 'paid'){
                    $stage = $this->requestRepository->getStatusEnumBySlug($req->stage->slug);
                    $rq = [
                        'user_id' => $user->id,
                        'request_status_id' => $stage->id,
                        'checked' => 1,
                        'updated_by' => $updated_by ? $updated_by->id : $user->id,
                    ];
                    array_push($memRequests, $rq);
                }
                if($req->rejected){
                    $rq = [
                        'user_id' => $user->id,
                        'request_status_id' => 1,
                        'checked' => 1,
                        'rejected' => $stage->id,
                        'updated_by' => $updated_by ? $updated_by->id : $user->id,
                    ];
                    array_push($memRequests, $rq);
                }
            }
            foreach($memRequests as $request){
                $this->memberRepository->createRequest($request);
            }
    
            $ppfront = $this->saveFileFromUrl($req->user_id, $req->user->details->ppfront, null, 'ppf');
            $ppback = $this->saveFileFromUrl($req->user_id, $req->user->details->ppback, null, 'ppb');
            $cidfront = $this->saveFileFromUrl($req->user_id, $req->user->details->cidfront, null, 'cvf');
            $cidback = $this->saveFileFromUrl($req->user_id, $req->user->details->cidback, null, 'cvb');
            $memberDetails = [
                'member_unit_id' => $memberUnit->id,
                'civil_id' => $req->user->details->civil_id,
                'dob' => $req->user->details->dob,
                'whatsapp' => $req->user->details->whatsapp,
                'whatsapp_code' => $req->user->details->whatsapp_calling_code,
                'emergency_phone' => $req->user->details->emergency_phone,
                'emergency_phone_code' => $req->user->details->emergency_phone_code,
                'company' => $req->user->details->company,
                'profession' => $req->user->details->profession,
                'company_address' => $req->user->details->company_address,
                'passport_no' => $req->user->details->passport_no,
                'passport_expiry' => $req->user->details->passport_expiry,
                'photo_civil_id_front' => $cidfront,
                'photo_civil_id_back' => $cidback,
                'photo_passport_front' => $ppfront,
                'photo_passport_back' => $ppback,
                'paci' => $req->user->details->paci,
                'sndp_branch' => $req->sndp_branch,
                'sndp_branch_number' =>$req->sndp_branch_number,
                'sndp_union' => $req->sndp_union,
                'completed' => 1
            ];
            $this->memberRepository->updateOrCreateMemberDetails($user->id, $memberDetails);
    
            $memberLocalAddress = [
                'governorate' => $req->governorate,
                'line_1' => $req->user->details->la_1,
                'building' => $req->user->details->la_building,
                'flat' => $req->user->details->la_flat,
                'floor' => $req->user->details->la_floor,
                'country' => $req->user->details->la_country,
                'region' => $req->user->details->la_region,
                'district' => $req->user->details->la_city,
                'city' => $req->user->details->la_city,
                'zip' => $req->user->details->la_zip,
            ];
            $this->memberRepository->updateOrCreateLa($user->id, $memberLocalAddress);
    
            $memberPermanentAddress = [
                'line_1' => $req->user->details->pa_1,
                'line_2' => $req->user->details->pa_2,
                'country' => $req->user->details->pa_country,
                'region' => $req->user->details->pa_region,
                'district' => $req->user->details->pa_district,
                'city' => $req->user->details->pa_city,
                'zip' => $req->user->details->pa_zip,
                'contact' => $req->user->details->home_contact
            ];
            $this->memberRepository->updateOrCreatePa($user->id, $memberPermanentAddress);

            DB::commit();
            return;

        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        
    }

    public function getUserDetails(int $userId)
    {
        try {
            $response = Http::withHeaders($this->headers)->get($this->api.'migration/get/user', ['user_id' => $userId]);
            if($response->ok()){
                $response = $response->json();
                return $response['data']['user'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getMemberDetails(int $userId)
    {
        try {
            $response = Http::withHeaders($this->headers)->get($this->api.'migration/get/member', ['user_id' => $userId]);
            if($response->ok()){
                $response = $response->json();
                return $response['data']['member'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function saveFileFromUrl(int $user_id, string $imageUrl, ?string $storagePath = null, ?string $prefix = null)
    {
        try {
            if(!$imageUrl){
                return null;
            }
            $path = parse_url($imageUrl, PHP_URL_PATH); 
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $response = Http::get($imageUrl);
            $fileName = $prefix ?? ''; 
            $fileName = $fileName.$user_id.'_'.time().'.'.$extension;
            $filePath = $storagePath ? '/images/'.$storagePath.'/'.$fileName : '/images/'.$fileName ;
            Storage::disk('public')->put($filePath, $response->body());
            return $fileName;
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
