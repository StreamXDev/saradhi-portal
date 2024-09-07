@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Members</h1>
    </div>
</div>
<div class="page-content">
    <table class="table table-list">
        <tr>
            <th></th>
            <th>Name</th>
            <th></th>
        </tr>
        @forelse ($members as $member)    
        <tr>
            <td><img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->name }}" title="{{ $member->name }}" class="list-profile-photo" /></td>
            <td>{{ $member->name }}.</td>
            <td class="actions">
                <a href="/admin/members/member/view/{{ $member->user->id }}"><i class="fa-solid fa-eye"></i></a>
            </td>
        </tr>
        @empty
            No items found.
        @endforelse 
    </table>
    {!! $members->withQueryString()->links('pagination::bootstrap-5') !!}
</div>
@endsection