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
                <div class="col-md-4">
                    <input type="text" name="search_by" id="search_by" placeholder="Name/Email/Phone/MID" class="form-control" value="{{ $filters['search_by'] }}">
                </div>
                <div class="col-md-2">
                    <input type="submit" value="Search" class="btn btn-primary">
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