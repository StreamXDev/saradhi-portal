@extends('layouts.admin')
@section('page-style')
<style>
    .table tr td{
        vertical-align: middle
    }
</style>
@endsection
@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Members</h1>
        <div><small class="text-muted">Showing <strong>{{$members->currentPage()}}</strong> to <strong>{{$members->count()}}</strong> of <strong>{{$members->total()}}</strong> results</small></div>
    </div>
</div>
<div class="page-content">
    <table class="table list">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
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
                    <div style="width:50px; height:50px; border-radius:50%; display:block; overflow:hidden">
                    @if($member->user->avatar)
                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="list-profile-photo" style="width: 100%" />
                    @else
                        <img src="{{ $member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="" style="width: 100%; opacity:0.2">
                    @endif
                    </div>
                </td>
                <td>{{ ucwords(strtolower($member->name)) }}</td>
                <td>{{ $member->membership->mid }}</td>
                <td>{{ $member->details->member_unit->name }}</td>
                <td>{{ ucfirst($member->membership->type) }}</td>
                <td>{{ ucfirst($member->membership->status) }}</td>
                <td>
                    <div class="actions">
                        <a href="/admin/members/member/view/{{ $member->user->id }}/{{$members->currentPage()}}" class="btn"><i class="fa-solid fa-eye"></i></a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">{{ $members->links() }}</div>
</div>
@endsection