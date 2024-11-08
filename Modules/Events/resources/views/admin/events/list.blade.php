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
    <h1 class="title">{{ ucfirst($type) }} Events</h1>
    <div>
        <a href="/admin/events/create" class="btn btn-primary">Create New Event</a>
    </div>
</div>
<div class="page-content">
    <table class="table list event-list">
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Location</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($events as $event)    
            <tr>
                <td class="event-title">
                    <span class="event-thumb">
                        @if($event->thumb)<img src="{{ url('storage/images/events/'. $event->thumb) }}" alt="" class="event-list-thumb">
                        @else <div class="no-thumb"></div> @endif
                    </span>
                    {{$event->title}} &nbsp;
                    @if($event->start_date <= date('Y-m-d') && $event->end_date >= date('Y-m-d'))<span class="badge rounded-pill text-bg-danger">On Going</span>@endif
                </td>
                <td>
                    <span class="date">{{date('M d, Y', strtotime($event->start_date))}} {{date('h:i a', strtotime($event->start_time))}}</span>
                    &nbsp;{{$event->end_date ? 'To':''}}&nbsp;
                    <span class="date">{{date('M d, Y', strtotime($event->end_date))}} {{date('h:i a', strtotime($event->end_time))}}</span>
                </td>
                <td>{{$event->location}}</td>
                <td>
                    <div class="actions">
                        <a href="/admin/events/view/{{ $event->id }}" class="btn"><i class="fa-solid fa-eye"></i></a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection