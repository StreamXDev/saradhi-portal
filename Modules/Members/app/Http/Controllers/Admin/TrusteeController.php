<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Members\Exports\TrusteesListExport;
use Modules\Members\Models\MemberTrustee;

class TrusteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $trustees = MemberTrustee::with('user', 'member', 'member.membership')->orderBy('tid', 'asc')->paginate(25); 
        
        if($request->get('export')){
            return $this->exportListToExcel($trustees);
        }

        return view('members::admin.trustee.list', compact('trustees'));
    }

    private function exportListToExcel($trustees)
    {
        $trustees = MemberTrustee::with('user', 'member')->orderBy('tid', 'asc')->get();
        return Excel::download(new TrusteesListExport($trustees), 'trustees.xlsx');
    }
}
