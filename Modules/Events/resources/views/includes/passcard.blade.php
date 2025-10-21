
<div 
    class="eventPassCard" 
    id="idCard_{{$invitee->id}}" 
    style="
    background-image: url('{{ url('storage/images/events/'. $invitee->invitee_type->description) }}'); 
    background-color: {{$invitee->invitee_type->category}};
    width:@if($invitee->invitee_type->pass_width_cm) {{$invitee->invitee_type->pass_width_cm}}cm @else 10cm @endif;
    height:@if($invitee->invitee_type->pass_height_cm) {{$invitee->invitee_type->pass_height_cm}}cm @else 15cm @endif
">
    <div class="header">
        <div class="title">{{$event->title}}</div>
        <div class="info">{{date('M d, Y',strtotime($event->start_date))}} @if($event->end_date) - {{date('M d, Y',strtotime($event->end_date))}}@endif</div>
        @if($event->location)<div class="info">{{$event->location}}</div> @endif
    </div>
    <div class="sl-no">{{2471+$invitee->id}}</div>
    <div class="card-title">{{$invitee->invitee_type->name}}</div>
    <div class="qr-container">
        <div class="qr">{{ $invitee->idQr }}</div>
    </div>
    <div class="card-info">
        Admit {{ $invitee->pack_count }}
    </div>
    @if($invitee->company)<div class="card-info">{{ $invitee->company }}</div>@endif
    @if($invitee->designation)<div class="card-info mb-2">{{ $invitee->designation }}</div>@endif
    @if($invitee->unit)<div class="card-info">Unit: {{ $invitee->unit }}</div>@endif
</div>
<div style="flex: 1 0 100%; display:flex; justify-content:flex-end; margin-top:1rem; column-gap:0.5rem">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="margin-left: auto">Close</button>
    <button type="button" class="btn btn-primary screenshot" id="screenshot{{$invitee->id}}" data-id="{{$invitee->id}}" data-name="{{$invitee->name}}">Download</button>
</div>