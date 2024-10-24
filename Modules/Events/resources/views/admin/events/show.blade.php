@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div>
        <h1 class="title">Event</h1>
    </div>
</div>
<div class="page-content no-padding">
    <div class="event-view">
        <div class="event-view_cover">
            <div class="cover-image">
                @if($event->cover)<img src="{{ url('storage/images/events/'. $event->cover) }}" alt="">@endif
            </div>
        </div>
        <div class="event-view_content">
            <div class="event-header">
                <div class="event-title">
                    <h1 class="title">{{$event->title}}</h1>
                </div>
                <div>
                    <a href="/admin/events/{{$event->id}}/volunteers" class="btn btn-xs btn-primary">View Volunteers</a>
                    <a href="/admin/events/{{$event->id}}/invitees" class="btn btn-xs btn-primary">View Invitees</a>
                </div>
            </div>
            <ul class="nav nav-underline" id="eventTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab_overview" data-bs-toggle="tab" data-bs-target="#tab_overview-pane" type="button" role="tab" aria-controls="tab_overview-pane" aria-selected="true">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab_participants" data-bs-toggle="tab" data-bs-target="#tab_participants-pane" type="button" role="tab" aria-controls="tab_participants-pane" aria-selected="false">Participants</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab_volunteer" data-bs-toggle="tab" data-bs-target="#tab_volunteer-pane" type="button" role="tab" aria-controls="tab_volunteer-pane" aria-selected="false">Volunteers</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="tab_overview-pane" role="tabpanel" aria-labelledby="tab_overview" tabindex="0">
                    <p>{{$event->description}}</p>
                    <div>Start Date: {{date('M d,Y', strtotime($event->start_date))}}</div>
                    @if($event->end_date )<div>Start Date: {{date('M d,Y', strtotime($event->end_date))}}</div>@endif
                    @if($event->start_time )<div>Start Time: {{date('h:i a', strtotime($event->start_time))}}</div>@endif
                    @if($event->end_time )<div>Start Time: {{date('h:i a', strtotime($event->end_time))}}</div>@endif
                    @if($event->location)<div>Venue: {{$event->location}}</div>@endif
                </div>
                <div class="tab-pane fade" id="tab_participants-pane" role="tabpanel" aria-labelledby="tab_participants" tabindex="0">
                    <table class="table event-list">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Unit</th>
                                <th>Admitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($participants as $person)    
                            <tr>
                                <td>{{$person->name}}</td>
                                <td>{{$person->type}}</td>
                                <td>{{$person->unit}}</td>
                                <td>10.46 AM<span class="col-info block">by <a href="/admin/members/member/view/{{$person->admintted_by}}" target="_blank">Safeer Aslam</a></span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="tab_volunteer-pane" role="tabpanel" aria-labelledby="tab_volunteer" tabindex="0">
                    <table class="table event-list">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($volunteers as $volunteer)    
                            <tr>
                                <td>{{$volunteer->user->name}}</td>
                                <td>{{$volunteer->user->email}}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script>
    $(document).ready(function(){
        var url = window.location.href;
        var tabQuery = url.indexOf("#");
        var activeTab = url.substring(url.indexOf("#") + 1);
        console.log(tabQuery)
        if(tabQuery > 0){
            $(".tab-pane").removeClass("active show");
            $("#eventTab .nav-link").removeClass("active");
            $("#" + activeTab +'-pane').addClass("active show");
            $("#eventTab #"+activeTab).addClass("active");
        }
    });
</script>
@endsection