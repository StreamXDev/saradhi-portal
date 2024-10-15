@extends('layouts.admin')

@section('content')
<div class="page-title">
    <h1 class="title">Import Members</h1>
    <div>
        <a href="/admin/import/do?page={{ $members->currentPage() }}" class="btn btn-primary">Import Next Set</a>
    </div>
</div>
<div class="page-content">
    <div style="display: flex; align-items:stretch; column-gap:2rem">
        <div style="flex: 1">
            <h4>Success</h4>
            <div>{{ $members->links() }}</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>MID</th>
                        <th>Membership ID</th>
                        <th>Parent User ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $key => $member)    
                    <tr>
                        <td>{{ $members->firstItem() + $key }}</td>
                        <td>
                            <div style="width:50px; height:50px; border-radius:50%; display:block; overflow:hidden">
                                @if($member->user->avatar)
                                    <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="list-profile-photo" style="width: 100%" />
                                @else
                                    <img src="{{ $member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="" style="width: 100%; opacity:0.2">
                                @endif
                            </div>
                        </td>
                        <td>{{$member->user->name}}</td>
                        <td>{{$member->user->email}}</td>
                        <td>
                            @if ($member->type === 'primary')
                                <strong>{{ucfirst($member->type)}}</strong>
                            @else
                                {{ucfirst($member->type)}}
                            @endif
                        </td>
                        <td>{{$member->mid}}</td>
                        <td>{{$member->membership_id}}</td>
                        <td>{{$member->parent_user_id}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
        </div>
        
    </div>
</div>
@endsection
