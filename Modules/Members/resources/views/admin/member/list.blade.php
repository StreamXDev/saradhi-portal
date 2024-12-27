@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Members</h1>
        <div><small class="text-muted">Showing <strong>{{$members->currentPage()}}</strong> to <strong>{{$members->count()}}</strong> of <strong>{{$members->total()}}</strong> results</small></div>
    </div>
</div>
<div class="page-search">
    <div class="page-title box-title">
        <h2 class="title">Search &amp; Filter</h2>
    </div>
    <form action="" method="">
        <div class="form-group no-margin">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search_by" id="search_by" placeholder="Name/Email/Phone/MID" class="form-control" value="{{ $filters['search_by'] }}">
                </div>
                <div class="col-md-2">
                    <select name="status" id="search_status" class="form-select">
                        <option value="">Status</option>
                        <option value="active" @if($filters['status'] == 'active') selected @endif>Active</option>
                        <option value="dormant" @if($filters['status'] == 'dormant') selected @endif>Dormant</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="unit" id="search_unit" class="form-select">
                        <option value="">Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{$unit->id}}" @selected($filters['unit'] == $unit->id ? true : false)>{{$unit->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5" style="display: flex; align-items:center; column-gap:0.5rem">
                    <input type="submit" name="search" value="Search" class="btn btn-primary">
                    <input type="button" value="CLEAR" class="btn btn-outline-default" onClick="clearForm();">
                    <button type="submit" name="export" value="export" class="btn btn-outline-default btn-right" style="margin-left: auto"><i class="fa-regular fa-file-excel"></i> Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="page-content">
    <table class="table list">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Email</th>
                <th>MID</th>
                <th>Civil ID</th>
                <th>Unit</th>
                <th>Mem.Type</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $member)
            <tr>
                <td>
                    <div class="list-profile-photo">
                    @if($member->user->avatar)
                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" />
                    @else
                        <img src="{{ $member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="" style="width: 100%; opacity:0.2">
                    @endif
                    </div>
                </td>
                <td>{{ ucwords(strtolower($member->name)) }}</td>
                <td>{{ $member->user->email }}</td>
                <td>@if($member->membership) {{ $member->membership->mid }} @endif</td>
                <td><span class="@if($member->duplicate_civil_id)text-danger @endif">@if($member->details) {{ $member->details->civil_id }} @endif</span></td>
                <td>@if($member->details) {{ $member->details->member_unit->name }} @endif</td>
                <td>@if($member->membership) {{ ucfirst($member->membership->type) }} @endif</td>
                <td>@if($member->membership) {{ ucfirst($member->membership->status) }} @endif</td>
                <td>
                    <div class="actions">
                        <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editEmail_{{$member->id}}">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        @include('members::admin.member.edit.email')
                        <a href="/admin/members/member/view/{{ $member->user->id }}/{{$members->currentPage()}}" class="btn"><i class="fa-solid fa-eye"></i></a>
                    </div>
                </td>
            </tr>

            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">{{ $members->appends(request()->query())->links() }}</div>
    
</div>
@endsection
@section('page_scripts')
<script>
    function clearForm(){
        window.location = window.location.href.split("?")[0];
    }
</script>
@endsection