@extends('layouts.admin')


@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">View Committee</h1>
    </div>
    <div>
        <a href="/admin/committee/edit/{{$committee->id}}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>
<div class="page-content">
    <div>
        <div><strong>{{$committee->committee_type->name}}</strong></div>
        @if($committee->unit)<div><span class="label">Unit: </span><span>{{$committee->unit->name}}</span></div>@endif
        <div><span class="label">Formed On: </span><span>{{date('M d, Y', strtotime($committee->formed_on))}}</span></div>
    </div>
    <hr />
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>Designation</th>
                    <th>Member Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $member)
                <tr>
                    <td>{{$member->designation->name}}</td>
                    <td>
                        <div class="profile-pill">
                            <div class="avatar">
                                @if($member->user->avatar)
                                    <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" />
                                @else
                                    <img src="{{ $member->member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="" style="width: 100%; opacity:0.2">
                                @endif
                            </div>
                            <div class="details">
                                <div class="title">{{$member->user->name}}</div>
                                <div>{{$member->user->email}}</div>
                                <div>MID: {{$member->member->membership->mid}}</div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection