<div class="eventPassCard" id="idCard_{{$invitee->id}}" style="background-color: {{$invitee->invitee_type->category}}">
    <div class="header">
        <div class="title">{{$event->title}}</div>
        <div class="info">{{date('M d, Y',strtotime($event->start_date))}} @if($event->end_date) - {{date('M d, Y',strtotime($event->end_date))}}@endif</div>
        @if($event->location)<div class="info">{{$event->location}}</div> @endif
    </div>
    <div class="card-title">{{$invitee->invitee_type->name}}</div>
    <div class="qr-container">
        <div class="qr">{{ $invitee->idQr }}</div>
    </div>
    <div class="card-info">
        Admit {{ $invitee->pack_count }}
    </div>
</div>
<div style="flex: 1 0 100%; display:flex; justify-content:flex-end; margin-top:1rem; column-gap:0.5rem">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="margin-left: auto">Close</button>
    <button type="button" class="btn btn-primary" id="screenshot" data-id="{{$invitee->id}}" data-name="{{$invitee->name}}">Download</button>
</div>