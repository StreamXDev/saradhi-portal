<?php

namespace Modules\Events\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Events\Models\EventEnum;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('events::admin.events.list');
    }

    /**
     * Create event form
     */
    public function create()
    {
        
        $participant_types = EventEnum::select('id', 'slug', 'name')->where('type', 'participant_type')->get();

        return view('events::admin.events.create', compact('participant_types'));
    }
}
