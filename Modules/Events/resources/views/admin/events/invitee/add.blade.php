@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div>
        <h1 class="title">Add Invitiees</h1>
        <div class="subtitle">Event: {{$event->title}}</div>
    </div>
    <a href="/admin/events/{{$event->id}}/invitees" class="btn btn-xs btn-primary">Invitees</a>
</div>
<div class="page-content">
    <div class="section-title box-title">
        <h2 class="title">Add Invitees</h2>
    </div>
    <div class="form-container">
        <form action="{{ route('admin.events.invitee.add') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="event_id" value="{{$event->id}}">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2">
                        <label for="type" class="form-label">Invitee Type <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="type" id="type" class="form-select">
                                @foreach ($invitee_types as $type)
                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="name" class="form-label">Name <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="company" class="form-label">Company</label>
                        <div class="control-col">
                            <input type="text" name="company" id="company" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="designation" class="form-label">Designation</label>
                        <div class="control-col">
                            <input type="text" name="designation" id="designation" class="form-control">
                        </div>
                    </div>
                    @if($units)
                    <div class="col-md-2">
                        <label for="unit" class="form-label">Unit</label>
                        <div class="control-col">
                            <select name="unit" id="unit" class="form-select">
                                <option value="">Select</option>
                                @foreach ($units as $unit)
                                    <option value="{{$unit->name}}">{{$unit->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2">
                        <label for="pack" class="form-label">No. of Packs</label>
                        <div class="control-col">
                            <input type="number" name="pack" id="pack" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="" class="form-label">&nbsp;</label>
                        <div class="control-col">
                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('page_scripts')
<script>
    $(document).ready(function(){
        let typeSelected = $('#type option:selected').text();
        $('#name').val(typeSelected);
        $('#type').on('change', function(){
            typeSelected = $('#type option:selected').text();
            $('#name').val(typeSelected);
        });
    });
</script>
@endsection