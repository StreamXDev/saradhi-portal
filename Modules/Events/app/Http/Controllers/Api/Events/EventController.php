<?php

namespace Modules\Events\Http\Controllers\Api\Events;

use Carbon\Carbon;
use App\Http\Controllers\Api\BaseController;
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
        
        $user = Auth::user();
        $events = Event::where('start_date', '>=', Carbon::now())->orderBy('start_date', 'desc')->get();
        //$member_participants = [];
        //$packTotal = 0;
        foreach($events as $key => $event){
            $volunteers = EventVolunteer::where('event_id', $event->id)->get();
            foreach($volunteers as $volunteer){
                if($volunteer->user_id == $user->id){
                    $events[$key]->volunteer = true;
                }
            }
            $idQr = QrCode::format('png')->size(300)->generate(json_encode([
                'pType' => 'member',
                'user_id' => $user->id,
            ]));
            $events[$key]['idQr'] = 'data:image/png;base64, ' . base64_encode($idQr);
        }
        $data = [
            'events' => $events
        ];
        return $this->sendResponse($data);
    }


    /**
     * Admit members
     */
    



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
