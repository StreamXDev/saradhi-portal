<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Request;
use Modules\Members\Models\Member;
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

    /**
     * Show the users for creating.
     *
     * @return \Illuminate\Http\Response
     */
    public function autocomplete(Request $request)
    {
        $data = [];
        $data = Member::with('user')
                ->where('name', 'LIKE', '%'. $request->get('query'). '%')
                ->take(10)
                ->get();
       
        return response()->json($data);
    }

    public function create()
    {
        $committee_types = MemberEnum::select('id', 'slug', 'name', 'category')->where('type', 'committee_type')->get();
        $designations = MemberEnum::select('id', 'slug', 'name', 'category')->where('type', 'designation')->get();
        $units = MemberUnit::get();
        return view('members::admin.committee.create', compact('committee_types', 'designations', 'units'));
    }
}
