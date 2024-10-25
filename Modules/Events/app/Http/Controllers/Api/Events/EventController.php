<?php

namespace Modules\Events\Http\Controllers\Api\Events;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventParticipant;
use Modules\Events\Models\EventVolunteer;
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
        
        $user = Auth::user(); //This will be normal member
        $events = Event::where('start_date', '>=', Carbon::now())->orderBy('start_date', 'desc')->get();
        
        foreach($events as $key => $event){
            $volunteers = EventVolunteer::where('event_id', $event->id)->get();
            foreach($volunteers as $volunteer){
                if($volunteer->user_id == $user->id){
                    $events[$key]->volunteer = true;
                }
            }
            $idQr = QrCode::format('png')->size(300)->generate(json_encode(['E'.$event->id.'-U'.$user->id]));
            $events[$key]['idQr'] = 'data:image/png;base64, ' . base64_encode($idQr);
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

        $event = Event::where('id', $event_id)->first();
        if(!$event || $qType !== 'event'){
            return $this->sendError('Not allowed', 'No events found', 405); 
        }

        $volunteer = Auth::user();
        $isVolunteer = EventVolunteer::where('event_id',$event_id)->where('user_id', $volunteer->id)->where('active',1)->first();
        if(!$isVolunteer){
            return $this->sendError('Not allowed', 'Only registered volunteers can read the data', 405); 
        }
        
        $packTotal = $packBalance = 0;
        $member_participants = [];
        if($pType == 'member' && $event->invite_all_members && Module::has('Members')){
            $user = User::where('id',$user_id)->first();
            $member = Member::with('relations','relations.relatedMember.user','relations.relatedDependent','details')->where('user_id', $user->id)->first();
            $packTotal = 1;
            $member_admitted = EventParticipant::where('event_id',$event->id)->where('user_id',$user->id)->first();
            array_push($member_participants, [
                'pType' => 'member',
                'user_id' => $member->user_id,
                'relation' => $member->type,
                'name' => $member->name,
                'unit' => $member->details->member_unit->name,
                'admitted' => isset($member_admitted->admitted) && $member_admitted->admitted == 1 ? 1 : 0
            ]);


            $relations = $member->relations;
            if($relations){
                $packTotal = $packTotal + count($relations);
                foreach($relations as  $relation){
                    if($relation->relatedMember){
                        $relatedMember_admitted = EventParticipant::where('event_id',$event->id)->where('user_id', $relation->relatedMember->user->id)->first();
                        array_push($member_participants, [
                            'pType' => 'member',
                            'user_id' => $relation->relatedMember->user->id,
                            'parent_user_id' => $member->user_id,
                            'relation' => $relation->relatedMember->type,
                            'name' => $relation->relatedMember->name,
                            'unit' => $member->details->member_unit->name,
                            'admitted' => isset($relatedMember_admitted->admitted) && $relatedMember_admitted->admitted == 1 ? 1 : 0
                        ]);
                    }else if($relation->relatedDependent){
                        $relatedDependent_admitted = EventParticipant::where('event_id',$event->id)->where('dependent_id',$relation->relatedDependent->id)->first();
                        array_push($member_participants, [
                            'pType' => 'member_dependent',
                            'dependent_id' => $relation->relatedDependent->id,
                            'parent_user_id' => $member->user_id,
                            'relation' => $relation->relatedDependent->type,
                            'name' => $relation->relatedDependent->name,
                            'unit' => $member->details->member_unit->name,
                            'admitted' => isset($relatedDependent_admitted->admitted) && $relatedDependent_admitted->admitted == 1 ? 1 : 0
                        ]);
                    }
                }
            }
            $packBalance = $packTotal;
            foreach($member_participants as $participant){
                $packBalance -= (int)$participant['admitted'];
            }
        }else if($pType == 'invitee'){
            $invitee = EventParticipant::where('id',$invitee_id)->first();
            $member_participants = [
                [
                    'pType' => 'invitee',
                    'invitee_id' => $invitee->id,
                    'name' => $invitee->name,
                    'unit' => $invitee->unit,
                    'admitted' => $invitee->admitted
                ]
            ];
            $packTotal = $invitee->pack_count;
            $packBalance = $invitee->pack_count - $invitee->admit_count;
        }

        $data = [
            'invitees' => $member_participants,
            'packTotal' => $packTotal,
            'packBalance' => $packBalance
        ];
        return $this->sendResponse($data);
    }



    /*
            if($event->invite_all_members && Module::has('Members')){
                $member = Member::with('relations','relations.relatedMember.user','relations.relatedDependent','details')->where('user_id', $user->id)->first();
                $member_admitted = EventParticipant::where('event_id',$event->id)->where('user_id',$user->id)->first();
                $packTotal = 1;

                array_push($member_participants, [
                    'pType' => 'member',
                    'user_id' => $member->user_id,
                    'relation' => $member->type,
                    'name' => $member->name,
                    'unit' => $member->details->member_unit->name,
                    'admitted' => isset($member_admitted->admitted) && $member_admitted->admitted == 1 ? 1 : 0
                ]);
                

                $relations = $member->relations;
                if($relations){
                    $packTotal = $packTotal + count($relations);
                    foreach($relations as  $relation){
                        if($relation->relatedMember){
                            $relatedMember_admitted = EventParticipant::where('event_id',$event->id)->where('user_id', $relation->relatedMember->user->id)->first();
                            array_push($member_participants, [
                                'pType' => 'member',
                                'user_id' => $relation->relatedMember->user->id,
                                'parent_user_id' => $member->user_id,
                                'relation' => $relation->relatedMember->type,
                                'name' => $relation->relatedMember->name,
                                'unit' => $member->details->member_unit->name,
                                'admitted' => isset($relatedMember_admitted->admitted) && $relatedMember_admitted->admitted == 1 ? 1 : 0
                            ]);
                        }else if($relation->relatedDependent){
                            $relatedDependent_admitted = EventParticipant::where('event_id',$event->id)->where('dependent_id',$relation->relatedDependent->id)->first();
                            array_push($member_participants, [
                                'pType' => 'member_dependent',
                                'dependent_id' => $relation->relatedDependent->id,
                                'parent_user_id' => $member->user_id,
                                'relation' => $relation->relatedDependent->type,
                                'name' => $relation->relatedDependent->name,
                                'unit' => $member->details->member_unit->name,
                                'admitted' => isset($relatedDependent_admitted->admitted) && $relatedDependent_admitted->admitted == 1 ? 1 : 0
                            ]);
                        }
                    }
                }
                
            }
            
            $packBalance = $packTotal;
            foreach($member_participants as $participant){
                $packBalance -= (int)$participant['admitted'];
            }
                */

}
