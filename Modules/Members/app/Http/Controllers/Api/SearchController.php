<?php

namespace Modules\Members\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Members\Models\Member;
use Modules\Members\Models\MemberEnum;
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
        $members = Member::with(['membership', 'details','user'])->where('active', 1)->orderBy('id','desc');
        $filters = collect(
            [
                'search_by' => '',
                'unit' => '',
                'blood_group' => '',
                'status' => '',
            ]
        );
        if (request()->get('search_by') != null){
            $input = request()->get('search_by');
            $members->where('type', 'LIKE', '%' .$input. '%')
                ->orWhereHas('user', function($q) use ($input) {
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

        if (request()->get('blood_group') != null){
            $input = request()->get('blood_group');
            //$members->orWhereHas('details', function($q) use ($input) {
                //return $q->where('member_unit_id', $input);
            //});
            $members->where('blood_group', $input);
            $filters->put('blood_group', request()->get('blood_group'));
        }

        $data = [
            'members' => $members->paginate(),
            'filters' => $filters,
        ];
        return $this->sendResponse($data);
    }
}
