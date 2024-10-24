@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div>
        <h1 class="title">Invitiees</h1>
        <div class="subtitle">Event: {{$event->title}}</div>
    </div>
    <a href="/admin/events/{{$event->id}}/invitee/add" class="btn btn-xs btn-primary">Add Invitees</a>
</div>
<div class="page-content mb-2">
    <div class="count-boxes">
        <div class="item">
            <div class="title">Total</div>
            <div class="count"><label>Invited</label>{{$total_invited}}</div>
            <div class="count"><label>Attended</label>{{$total_attended}}</div>
        </div>
        @foreach ($participant_types as $type)
            @isset($group_count[$type->slug]) 
                <div class="item">
                    <div class="title">{{$type->name}}</div>
                    <div class="count"><label>Total</label>{{$group_count[$type->slug]['total']}} </div>
                    <div class="count"><label>Attended</label>{{$group_count[$type->slug]['attended']}} </div>
                </div> 
            @endisset
        @endforeach
    </div>
</div>
<div class="page-content">
    <table class="table event-list">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Company</th>
                <th>Designation</th>
                <th>Unit</th>
                <th>No. of Allowed</th>
                <th>No. of Admitted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invitees as $invitee)    
                <tr>
                    <td>{{$invitee->name}}</td>
                    <td>{{$invitee->invitee_type->slug}}</td>
                    <td>{{$invitee->company}}</td>
                    <td>{{$invitee->designation}}</td>
                    <td>{{$invitee->unit}}</td>
                    <td>{{$invitee->pack_count}}</td>
                    <td>{{$invitee->admit_count}}</td>
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

<!-- Modal -->
<div class="modal fade" id="eventPassModal" tabindex="-1" aria-labelledby="eventPassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="eventPassModalLabel">Admission Pass</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <div class="id-card-wrapper">
                @include('events::includes.passcard')
            </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="screenshot">Download</button>
        </div>
      </div>
    </div>
</div>
@endsection