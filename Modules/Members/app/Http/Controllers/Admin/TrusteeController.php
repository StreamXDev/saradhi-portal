<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Members\Models\MemberTrustee;

class TrusteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trustees = MemberTrustee::with('user', 'member')->orderBy('tid', 'asc')->paginate(25); 
        //dd($trustees);
        return view('members::admin.trustee.list', compact('trustees'));
    }
}
