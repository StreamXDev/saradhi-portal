<?php

namespace Modules\Members\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Members\Exports\TrusteesListExport;
use Modules\Members\Models\MemberTrustee;
use Modules\Members\Models\MemberUnit;

class TrusteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        list($trustees, $filters) = $this->trusteeSearch();
        $trustees = $trustees->paginate();
        //$trustees = MemberTrustee::with('user', 'member', 'member.membership')->orderBy('tid', 'asc')->paginate(25); 
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        if($request->get('export')){
            return $this->exportListToExcel($trustees);
        }
        //dd($trustees);
        return view('members::admin.trustee.list', compact('trustees','filters','units'));
    }

    public function trusteeSearch()
    {

        $trustees = MemberTrustee::with('user', 'member', 'member.membership', 'member.details')->orderBy('tid', 'asc');

        $filters = collect(
            [
                'search_by' => '',
                'unit' => '',
                'status' => '',
            ]
        );
        if (request()->get('unit') != null){
            $input = request()->get('unit');
            $trustees->whereHas('member.details', function($q) use ($input) {
                return $q->where('member_unit_id', request()->get('unit'));
            });
            $filters->put('unit', request()->get('unit'));
        }
        if (request()->get('status') != null){
            $input = request()->get('status');
            $trustees->where('status', $input);
            $filters->put('status', request()->get('status'));
        }
        
        if (request()->get('search_by') != null){
            $search = request()->get('search_by');
            $trustees->when($search, function ($query, $search) {
                $query->where(function ($_query) use ($search) {
                    $_query->where('tid', $search)
                        ->orWhereHas('user', function ($query) use ($search) {
                            return $query->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('user', function($query) use ($search) {
                            return $query->where('email', $search);
                        })
                        ->orWhereHas('user', function($query) use ($search) {
                            return $query->where('phone', $search);
                        })
                        ->orWhereHas('member.membership', function($query) use ($search) {
                            return $query->where('mid', $search);
                        });
                });
            });
            $filters->put('search_by', request()->get('search_by'));
        }

        return [
            $trustees,
            $filters
        ];
    }

    private function exportListToExcel($trustees)
    {
        list($trustees) = $this->trusteeSearch();
        $trustees = $trustees->get();
        //$trustees = MemberTrustee::with('user', 'member')->orderBy('tid', 'asc')->get();
        return Excel::download(new TrusteesListExport($trustees), 'trustees.xlsx');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $trustee = MemberTrustee::findOrFail($id);

        $trustee->delete();

        return redirect()->back()->with(
            ['message' => 'Trustee has been Deleted']
        );
    }
}
