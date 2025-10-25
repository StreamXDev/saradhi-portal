<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MemberUnit;

class SearchController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $units = MemberUnit::select('id', 'slug', 'name')->where('active', 1)->get();
        $blood_groups = MemberEnum::select('id', 'slug', 'name')->where('type', 'blood_group')->get();
        $data = [
            'units' => $units,
            'blood_groups' => $blood_groups,
            'types'=> ['search_by','unit','blood_group']
        ];
        return $this->sendResponse($data);
    }

    public function search()
    {
        $members = Member::with(['membership', 'details','user'])->orderBy(Membership::select('mid')->whereColumn('memberships.user_id', 'members.user_id'))->where('active', 1);
        $filters = collect(
            [
                'search_by' => '',
                'unit' => '',
                'blood_group' => '',
                'status' => '',
            ]
        );
        
        if (request()->get('status') != null){
            $input = request()->get('status');
            $members->whereHas('membership', function($q) use ($input) {
                return $q->where('status', $input);
            });
            $filters->put('status', request()->get('status'));
        }

        if (request()->get('unit') != null){
            $input = request()->get('unit');
            $members->whereHas('details', function($q) use ($input) {
                return $q->where('member_unit_id', request()->get('unit'));
            });
            $filters->put('unit', request()->get('unit'));
        }

        if (request()->get('blood_group') != null){
            $input = request()->get('blood_group');
            $members->where('blood_group',  $input);
            $filters->put('blood_group', request()->get('blood_group'));
        }

        if (request()->get('search_by') != null){
            $search = request()->get('search_by');
            $members->when($search, function ($query, $search) {
                $query->where(function ($_query) use ($search) {
                    $_query->orWhereHas('user', function ($query) use ($search) {
                        return $query->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('user', function($query) use ($search) {
                        return $query->where('email', $search);
                    })
                    ->orWhereHas('user', function($query) use ($search) {
                        return $query->where('phone', $search);
                    })
                    ->orWhereHas('membership', function($query) use ($search) {
                        return $query->where('mid', $search);
                    });
                });
            });
            $filters->put('search_by', request()->get('search_by'));
        }
        /*
        if (request()->get('search_by') != null){
            $input = request()->get('search_by');
            $members->whereHas('user', function($q) use ($input) {
                return $q->where('name', 'LIKE', '%' . $input . '%');
            })
            ->orWhereHas('user', function($q) use ($input) {
                return $q->where('email', $input);
            })
            ->orWhereHas('user', function($q) use ($input) {
                return $q->where('phone', $input);
            })
            ->orWhereHas('membership', function($q) use ($input) {
                return $q->where('mid', $input);
            });

            $filters->put('search_by', request()->get('search_by'));
        }
            */

        $results  = $members->paginate(20);
        foreach($results as $key => $result){
            if($result->user->avatar){
                $results[$key]->user->avatar = url('storage/images/'. $result->user->avatar);
            }
        }

        $data = [
            'members' => $results,
            'filters' => $filters,
        ];
        return $this->sendResponse($data);
    }
}
