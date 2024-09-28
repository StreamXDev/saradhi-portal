<?php

namespace Modules\Imports\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Imports\Models\Export;
use Modules\Imports\Models\Member;
use Modules\Imports\Models\Membership;

class ImportMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('imports::index');
    }


    public function import()
    {
        $last_exported  = Export::select('membership_id')->latest()->first();

        if($last_exported){
            $membership = Membership::with('member')->where('id', '>', $last_exported->membership_id)->limit(5)->get();
        }else{
            $membership = Membership::with('member')->limit(5)->get();
        }

        


    }
}
