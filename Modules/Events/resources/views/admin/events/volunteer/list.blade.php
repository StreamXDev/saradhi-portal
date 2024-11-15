@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div>
        <h1 class="title">Volunteers</h1>
        <div class="subtitle">Event: {{$event->title}}</div>
    </div>
    <div>
        <a href="/admin/events/view/{{$event->id}}" class="btn btn-xs btn-outline-primary">View Event</a>
        @if($event->end_date >= date('Y-m-d'))<a href="/admin/events/{{$event->id}}/volunteer/add" class="btn btn-xs btn-primary">Add Volunteer</a>@endif
    </div>
</div>
<div class="page-content">
    <table class="table event-list">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($volunteers as $volunteer)    
                <tr>
                    <td>{{$volunteer->user->name}}</td>
                    <td>{{$volunteer->user->email}}</td>
                    <td>
                        <div class="actions">
                            <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#eventPassModal"><i class="icon" data-feather="credit-card"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection