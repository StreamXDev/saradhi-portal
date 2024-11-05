<?php

namespace Modules\Events\Http\Controllers\Api\Events;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventEnum;
use Modules\Events\Models\EventParticipant;
use Modules\Events\Models\EventVolunteer;
use Modules\Events\Models\Test;
use Modules\Members\Models\Member;
use Nwidart\Modules\Facades\Module;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use function PHPUnit\Framework\isEmpty;

class EventController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $user = Auth::user(); //The member logged in app
        $events = Event::where('end_date', '>=', Carbon::now())->orderBy('start_date', 'desc')->get();
        $member = Member::with('relations','relations.relatedMember.user','relations.relatedDependent','details', 'membership')->where('user_id', $user->id)->first();
        $relations = $member->relations;
        foreach($events as $key => $event){
            $events[$key]['pack']  = 0;
            $volunteers = EventVolunteer::where('event_id', $event->id)->get();
            foreach($volunteers as $volunteer){
                if($volunteer->user_id == $user->id){
                    $events[$key]->volunteer = true;
                }
            }
            if(!$event->invite_all_members){
                // if the event not invited all members, Check user is invited separately
                $invitee = EventParticipant::where('event_id', $event->id)->where('user_id', $user->id)->first();
                $event->invited = $invitee ? true : false;
                $events[$key]['pack'] = 1;

                if($relations){
                    foreach($relations as  $relation){
                        if($relation->relatedMember){
                            $relatedMember_invited = EventParticipant::where('event_id',$event->id)->where('user_id', $relation->relatedMember->user->id)->first();
                            if($relatedMember_invited) {
                                $events[$key]['pack']++;
                            }
                        }else if($relation->relatedDependent){
                            $relatedDependent_invited = EventParticipant::where('event_id',$event->id)->where('dependent_id',$relation->relatedDependent->id)->first();
                            if($relatedDependent_invited) {
                                $events[$key]['pack']++;
                            }
                        }
                    }
                }
            }else{
                $event->invited = true;
                if($relations){
                    $events[$key]['pack'] =  count($relations) + 1;
                }
                // total number of invitees = total number of dependents + primary member
            }
            if($member->membership->status == 'active'){
                $idQr = QrCode::format('png')->size(300)->generate(json_encode(['E'.$event->id.'-U'.$user->id]));
                $events[$key]['idQr'] = 'data:image/png;base64, ' . base64_encode($idQr);
            }
            $events[$key]['thumb'] = $event->thumb ? url('storage/images/events/'. $event->thumb) : null;
        }
        $data = [
            'events' => $events
        ];
        return $this->sendResponse($data);
    }


    /**
     * Admit members
     *
     */
    public function admitCreate(Request $request)
    {
        
        $input = $request->all();
        $qrString = $input['data'];
        $qrExplode = explode("-",$qrString);
        $qType = null;
        $event_id = null;
        $user_id = null;
        $invitee_id = null;

        if(substr($qrExplode[0], 0, 1) == 'E'){
            $qType = 'event';
            $event_id = (int)substr($qrExplode[0], 1);
        }
        if(substr($qrExplode[1], 0, 1) == 'U'){
            $pType = 'member';
            $user_id = (int)substr($qrExplode[1],1);
        }else if(substr($qrExplode[1], 0, 1) == 'I'){
            $pType = 'invitee';
            $invitee_id = (int)substr($qrExplode[1],1);
        }

        if(!$event_id || !$qType){
            return $this->sendError('Not allowed', 'No events found', 405); 
        }

        $event = Event::where('id', $event_id)->where('end_date', '>=', Carbon::now())->first();
        if(!$event || $qType !== 'event'){
            return $this->sendError('Not allowed', 'No upcoming events found', 405); 
        }

        $volunteer = Auth::user(); 
        $isVolunteer = EventVolunteer::where('event_id',$event_id)->where('user_id', $volunteer->id)->where('active',1)->first();
        if(!$isVolunteer){
            return $this->sendError('Not allowed', 'Only registered volunteers can read the data', 405); 
        }
        
        $packTotal = $packBalance = 0;
        $member_participants = [];
        $invitee_member_type = EventEnum::select('id', 'slug', 'name')->where('type', 'participant_type')->where('slug', 'member')->first();
        $invitee_dependent_type = EventEnum::select('id', 'slug', 'name')->where('type', 'participant_type')->where('slug', 'member_dependent')->first();
        if($pType == 'member' && $event->invite_all_members && Module::has('Members')){
            if(!$user_id){
                return $this->sendError('Invalid User', 'User not found', 405); 
            }
            $user = User::where('id',$user_id)->first();
            $member = Member::with('relations','relations.relatedMember.user','relations.relatedDependent','details')->where('user_id', $user->id)->first();
            $packTotal = 1;
            $member_admitted = EventParticipant::where('event_id',$event->id)->where('user_id',$user->id)->first();
            array_push($member_participants, [
                'pType' => 'member',
                'event_id' => $event->id,
                'user_id' => $member->user_id,
                'relation' => $member->type,
                'name' => $member->name,
                'unit' => $member->details->member_unit->name,
                'type' => $invitee_member_type->id,
                'admitted' => isset($member_admitted->admitted) && $member_admitted->admitted == 1 ? true : false,
                'pack_count' => 1,
                'admit_count' => isset($member_admitted->admitted) && $member_admitted->admitted == 1 ? 1 : 0,
            ]);


            $relations = $member->relations;
            if($relations){
                $packTotal = $packTotal + count($relations);
                foreach($relations as  $relation){
                    if($relation->relatedMember){
                        $relatedMember_admitted = EventParticipant::where('event_id',$event->id)->where('user_id', $relation->relatedMember->user->id)->first();
                        
                        array_push($member_participants, [
                            'pType' => 'member',
                            'event_id' => $event->id,
                            'user_id' => $relation->relatedMember->user->id,
                            'parent_user_id' => $member->user_id,
                            'relation' => $relation->relatedMember->type,
                            'name' => $relation->relatedMember->name,
                            'unit' => $member->details->member_unit->name,
                            'type' => $invitee_member_type->id,
                            'admitted' => isset($relatedMember_admitted->admitted) && $relatedMember_admitted->admitted == 1 ? true : false,
                            'pack_count' => 1,
                            'admit_count' => isset($relatedMember_admitted->admitted) && $relatedMember_admitted->admitted == 1 ? 1 : 0,
                        ]);
                    }else if($relation->relatedDependent){
                        $relatedDependent_admitted = EventParticipant::where('event_id',$event->id)->where('dependent_id',$relation->relatedDependent->id)->first();
                        array_push($member_participants, [
                            'pType' => 'member_dependent',
                            'event_id' => $event->id,
                            'dependent_id' => $relation->relatedDependent->id,
                            'parent_user_id' => $member->user_id,
                            'relation' => $relation->relatedDependent->type,
                            'name' => $relation->relatedDependent->name,
                            'unit' => $member->details->member_unit->name,
                            'type' => $invitee_dependent_type->id,
                            'admitted' => isset($relatedDependent_admitted->admitted) && $relatedDependent_admitted->admitted == 1 ? true : false,
                            'pack_count' => 1,
                            'admit_count' => isset($relatedDependent_admitted->admitted) && $relatedDependent_admitted->admitted == 1 ? 1 : 0,
                        ]);
                    }
                }
            }
            $packBalance = $packTotal;
            foreach($member_participants as $participant){
                $packBalance -= (int)$participant['admitted'];
            }
        }else if($pType == 'invitee'){
            if(!$invitee_id){
                return $this->sendError('Invalid Invitee', 'Invitee not found', 405); 
            }
            $invitee = EventParticipant::where('id',$invitee_id)->first();
            $member_participants = [
                [
                    'pType' => 'invitee',
                    'event_id' => $event->id,
                    'invitee_id' => $invitee->id,
                    'name' => $invitee->name,
                    'unit' => $invitee->unit,
                    'admitted' => $invitee->admitted,
                    'pack_count' => $invitee->pack_count,
                    'admit_count' => $invitee->admit_count,
                ]
            ];
            $packTotal = $invitee->pack_count;
            $packBalance = $invitee->pack_count - $invitee->admit_count;
        }

        $data = [
            'invitees' => $member_participants,
            'packTotal' => $packTotal,
            'packBalance' => $packBalance,
            'event' => $event
        ];
        return $this->sendResponse($data);
    }

    public function admitStore(Request $request)
    {
        $volunteer = Auth::user();
        $input = $request->all();
        $admitted = 0;
        $event = Event::where('id', $input['event_id'])->where('end_date', '>=', Carbon::now())->first();
        if(!$event){
            return $this->sendError('Not allowed', 'No upcoming events found', 405); 
        }
        foreach($input['admits'] as $admit){
            if($admit['pType'] == 'invitee'){
                //required : event_id, invitee_id , admit_count = 1
                $ep = EventParticipant::where('event_id',$admit['event_id'])->where('invitee_id', $admit['invitee_id'])->first();
                if(!$ep->admitted){
                    $ep->update([
                        'admitted' => 1,
                        'admitted_by' => $volunteer->id,
                        'admitted_on' => now()
                    ]);
                    $ep->increment('admit_count');
                    $admitted++;
                }
            }else{
                if($admit['pType'] == 'member'){
                    $ep = EventParticipant::where('event_id',$admit['event_id'])->where('user_id', $admit['user_id'])->first();
                    if(!$ep || !$ep->admitted){
                        $nep = EventParticipant::create([
                            'event_id' => $admit['event_id'],
                            'type' => $admit['type'],
                            'user_id' => $admit['user_id'],
                            'relation' => $admit['relation'],
                            'name' => $admit['name'],
                            'admitted' => 1,
                            'admitted_by' => $volunteer->id,
                            'admitted_on' => now(),
                            'created_by' => $volunteer->id,
                        ]);
                        $nep->increment('admit_count');
                        $admitted++;
                    }
                }else if($admit['pType'] == 'member_dependent'){
                    $ep = EventParticipant::where('event_id',$admit['event_id'])->where('dependent_id', $admit['dependent_id'])->first();
                    if(!$ep || !$ep->admitted){
                        $nep = EventParticipant::create([
                            'event_id' => $admit['event_id'],
                            'type' => $admit['type'],
                            'dependent_id' => $admit['dependent_id'],
                            'parent_user_id' => $admit['parent_user_id'],
                            'relation' => $admit['relation'],
                            'name' => $admit['name'],
                            'admitted' => 1,
                            'admitted_by' => $volunteer->id,
                            'admitted_on' => now(),
                            'created_by' => $volunteer->id,
                        ]);
                        $nep->increment('admit_count');
                        $admitted++;
                    }
                }
            }
        }
        $data = [
            'packTotal' => $input['packTotal'],
            'packAdmitted' => $admitted,
            'packBalance' => $input['packTotal'] - $admitted
        ];
        return $this->sendResponse($data);
    }
}
