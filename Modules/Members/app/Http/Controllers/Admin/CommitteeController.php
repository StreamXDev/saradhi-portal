<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Members\Models\MemberCommittee;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MemberUnit;

class CommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $committees = MemberCommittee::where('active', 1)->paginate(25); 
        //dd($committees);
        return view('members::admin.committee.list', compact('committees'));
    }

    public function create()
    {
        $committee_types = MemberEnum::select('id', 'slug', 'name')->where('type', 'committee_type')->get();
        $units = MemberUnit::get();
        return view('members::admin.committee.create', compact('committee_types','units'));
    }
}
