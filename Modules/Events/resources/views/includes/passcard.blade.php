<div class="eventPassCard">
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
        Admit 1
    </div>
</div>