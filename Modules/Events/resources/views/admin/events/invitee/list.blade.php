@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div>
        <h1 class="title">Invitiees</h1>
        <div><small class="text-muted">Showing <strong>{{$invitees->currentPage()}}</strong> to <strong>{{$invitees->count()}}</strong> of <strong>{{$invitees->total()}}</strong> results for {{$event->name}}</small></div>
    </div>
    <div>
        <a href="/admin/events/view/{{$event->id}}" class="btn btn-xs btn-outline-primary">View Event</a>
        <a href="/admin/events/{{$event->id}}/invitee/add" class="btn btn-xs btn-primary">Add Invitees</a>
    </div>
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
                            <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#eventPassModal{{$invitee->id}}" ><i class="icon" data-feather="credit-card"></i></a>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="eventPassModal{{$invitee->id}}" tabindex="-1" aria-labelledby="eventPassModal{{$invitee->id}}Label" aria-hidden="true">
                            <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h1 class="modal-title fs-5" id="eventPassModal{{$invitee->id}}Label">Admission Pass</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    
                                    <div class="id-card-wrapper">
                                        @include('events::includes.passcard')
                                    </div>

                                </div>
                            </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">{{ $invitees->links() }}</div>
</div>
@endsection

@section('page_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-zoom/1.6.1/jquery.zoom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    $('#screenshot').click(function(){
        var id = $(this).data('id');
        var name = $(this).data('name');
        var link = document.createElement('a');
        html2canvas(document.getElementById('idCard_'+id)).then(function(canvas) {
            var image = canvas.toDataURL();
            link.setAttribute('download', name+'_'+id+'_Member-ID.png');
            link.href = image;
            link.click();
        });
    });
</script>
@endsection