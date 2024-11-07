<?php

namespace Modules\Events\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventEnum;
use Modules\Events\Models\EventParticipant;
use Modules\Events\Models\EventVolunteer;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberUnit;
use Nwidart\Modules\Facades\Module;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use function PHPUnit\Framework\isEmpty;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        if($request->get('type') && $request->get('type') == 'past'){
            $events = $events->where('end_date', '<', date('Y-m-d'));
            $type = 'past';
        }else{
            $events = $events->where('end_date', '>=', date('Y-m-d'));
            $type = 'upcoming';
        }

        return view('events::admin.events.list', compact('events','type'));
    }


    /**
     * Show event
     */
    public function show(Request $request, $id)
    {
        $event = Event::where('id', $id)->first();
        $volunteers = EventVolunteer::with('user')->where('event_id', $id)->get();
        $participants = EventParticipant::where('event_id', $id)->where('admitted',1)->get();
        return view('events::admin.events.show', compact('event','volunteers', 'participants'));
    }

    /**
     * Create event form
     */
    public function create()
    {
        
        $participant_types = EventEnum::select('id', 'slug', 'name')->where('type', 'participant_type')->get();
        $memberModule = Module::has('Members');
        return view('events::admin.events.create', compact('participant_types','memberModule'));
    }

    /**
     * Show the users for creating a new event.
     *
     * @return \Illuminate\Http\Response
     */
    public function autocomplete(Request $request)
    {
        $data = [];
        if(Module::has('Members')){
            $data = Member::with('user')
                        ->where('name', 'LIKE', '%'. $request->get('query'). '%')
                        ->take(10)
                        ->get();
        }
       
        return response()->json($data);
    }

    /**
     * Storing eventS
     */
    public function store(Request $request){

        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
        ],[
            'title.required'    => 'Title is required',
            'start_date.required'    => 'Start date is required',
            'start_date.date_format'    => 'Start date should be of format Y-m-d',
            'end_date.required'    => 'End date is required',
            'end_date.date_format'    => 'End date should be of format Y-m-d',
        ]);

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }
        
        $input = $request->all();

        if(isset($input['thumb'])){
            $thumbName = 'event_thumb_'.time().'.'.$request->thumb->extension(); 
            $request->thumb->storeAs('public/images/events', $thumbName);
            $input['thumb'] = $thumbName;
        }
        if(isset($input['cover'])){
            $coverName = 'event_cover_'.time().'.'.$request->cover->extension(); 
            $request->cover->storeAs('public/images/events', $coverName);
            $input['cover'] = $coverName;
        }

        DB::beginTransaction();
        $event = Event::create([
            'title' => $input['title'],
            'description' => $input['description'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'start_time' => $input['start_time'],
            'end_time' => $input['end_time'],
            'location' => $input['location'],
            'thumb' => $input['thumb'],
            'cover' => $input['cover'],
        ]);

        if(isset($input['volunteers']) && $input['volunteers'] !== null){
            $volunteers = $input['volunteers'];
            foreach($volunteers as $volunteer_user_id){
                EventVolunteer::create([
                    'event_id' => $event->id,
                    'user_id' => (int)$volunteer_user_id,
                    'added_by' => $user->id,
                    'added_on' => now()
                ]);
            };
        }

        DB::commit();

        return redirect('/admin/events/'.$event->id.'/invitee/add');
    }

    
    // ---------------------------------------------------------- INVITEES ---------------------------------------------- //

    /**
     * Invitees list
     */
    public function invitees(Request $request, $id)
    {
        $event = Event::where('id', $id)->first();
        if(!$event){
            return redirect('/admin/events/create');
        }
        $participant_types = EventEnum::select('id', 'slug', 'name', 'category')->where('type', 'participant_type')->get();
        $data = EventParticipant::with('invitee_type')->where('event_id', $id);
        if($event->invite_all_members){
            $data->where('type' , '!=', 4)->orWhere('type', '!=', 5);
        }
        $invitee_count = $data->get();
        $invitees = $data->orderBy('id','desc')->paginate(20);
        $total_invited = 0;
        $total_attended = 0;
        $group_count = [];
        foreach($invitee_count as $invitee){
            $total_invited += $invitee->pack_count;
            $total_attended += $invitee->admit_count;
            foreach($participant_types as $key => $type){
                if($invitee->type == $type->id){
                    $group_count[$type->slug]['total'] = isset($group_count[$type->slug]['total']) ? $group_count[$type->slug]['total'] + $invitee->pack_count : $invitee->pack_count;
                    $group_count[$type->slug]['attended'] = isset($group_count[$type->slug]['attended']) ? $group_count[$type->slug]['attended'] + $invitee->admit_count : $invitee->admit_count;
                }
            }   
        }
        foreach($invitees as $key => $invitee){
            $invitees[$key]['idQr'] = QrCode::size(300)->generate(json_encode(['E'.$event->id.'-I'.$invitee->id]));
        }
        return view('events::admin.events.invitee.list', compact('invitees', 'event', 'total_invited', 'total_attended', 'participant_types', 'group_count'));
    }


    
    /**
     * Invitee add/edit form
     */
    public function createInvitee(Request $request, $id)
    {
        $event = Event::where('id', $id)->where('start_date', '>=', date('Y-m-d'))->first();
        if(!$event){
            return redirect('/admin/events/create');
        }
        
        $invitee_types = EventEnum::where('type', 'participant_type')->orderBy('order','asc')->get();
        if($event->invite_all_members){
            foreach ($invitee_types as $key => $item) {
                if ($item->slug == 'member' || $item->slug == 'member_dependent') {
                    unset($invitee_types[$key]);
                }
            }
        }
        $units = null;
        if(Module::has('Members')){
            $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        }
        return view('events::admin.events.invitee.add', compact('event', 'invitee_types', 'units'));
    }

    /**
     * Storing Invitees
     */
    public function storeInvitee(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'type' => 'required',
            'name' => 'required',
        ],[
            'event_id.required'    => 'Event ID is required',
            'type.required'    => 'Event type is required',
            'name.required'    => 'Name is required',
        ]);

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }

        $packs = isset($input['pack']) ? $input['pack'] : 1;
        $bulk = isset($input['bulk']) ? true : false;
        DB::beginTransaction();
        if($bulk){
            EventParticipant::create([
                'event_id' => $input['event_id'],
                'type' => $input['type'],
                'name' => $input['name'],
                'company' => $input['company'],
                'designation' => $input['designation'],
                'unit' => $input['unit'],
                'pack_count' => $packs,
                'created_by' => $user->id,
            ]);
        }else{
            for($i=1; $i <= $packs; $i++){
                EventParticipant::create([
                    'event_id' => $input['event_id'],
                    'type' => $input['type'],
                    'name' => $input['name'],
                    'company' => $input['company'],
                    'designation' => $input['designation'],
                    'unit' => $input['unit'],
                    'pack_count' => 1,
                    'created_by' => $user->id,
                ]);
            }
        }

        DB::commit();
        return redirect('/admin/events/'.$input['event_id'].'/invitees');
    }


    /* ---------------------------------------------------------- VOLUNTEERS -----------------------------------------------*/

    /**
     * Invitees list
     */
    public function volunteers(Request $request, $id)
    {
        $event = Event::where('id', $id)->first();
        if(!$event){
            return redirect('/admin/events/create');
        }
        $volunteers = EventVolunteer::with('user')->where('event_id', $id)->get();
        return view('events::admin.events.volunteer.list', compact('event','volunteers'));
    }


    /**
     * Volunteer Create form
     */
    public function createVolunteer(Request $request, $id)
    {
        $event = Event::where('id', $id)->where('start_date', '>=',  date('Y-m-d'))->first();
        if(!$event){
            return redirect('/admin/events/create');
        }
        return view('events::admin.events.volunteer.add', compact('event'));
    }

    /**
     * Store volunteers
     */
    public function storeVolunteer(Request $request){
        $user = Auth::user();
        $input = $request->all();
        $event = Event::where('id', $input['event_id'])->first();
        if(!$event){
            return redirect('/admin/events/create');
        }
        DB::beginTransaction();
        if($input['volunteers'] && $input['volunteers'] !== null){
            $volunteers = $input['volunteers'];
            foreach($volunteers as $volunteer_user_id){
                EventVolunteer::create([
                    'event_id' => $event->id,
                    'user_id' => (int)$volunteer_user_id,
                    'added_by' => $user->id,
                    'added_on' => now()
                ]);
            };
        }

        DB::commit();
        return redirect('/admin/events/view/'.$input['event_id'].'#tab_volunteer');
    }

}



