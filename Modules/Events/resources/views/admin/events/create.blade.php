<div>
    <!-- It is never too late to be what you might have been. - George Eliot -->
</div>
@extends('layouts.admin')
@section('page-style')
<style>
    .section-volunteers{
        margin: 1.5rem 0;
    }
    .volunteer-list{
        margin-top: 1.5rem
    }
</style>
@endsection
@section('content')
<div class="page-title">
    <h1 class="title">Create Event</h1>
</div>
<div class="page-content">
    @if(!$memberModule)
        <div>You should activate Members module to create events</div>
    @else
    <div class="form-container">
        <form action="{{ route('admin.events.create') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title" class="form-label">Event Title <span class="asterisk">*</span></label>
                <div class="form-col">
                    <input type="text" name="title" id="title" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Event Description</label>
                <div class="form-col">
                    <textarea name="description" id="description" class="form-control"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date <span class="asterisk">*</span></label>
                    <div class="form-col">
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <div class="form-col">
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <div class="form-col">
                        <input type="time" name="start_time" id="start_time" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <div class="form-col">
                        <input type="time" name="end_time" id="end_time" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="location" class="form-label">Location</label>
                    <div class="form-col">
                        <input type="text" name="location" id="location" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="thumb" class="form-label">Thumbnail Image</label>
                    <div class="form-col">
                        <input type="file" name="thumb" id="thumb">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="cover" class="form-label">Cover Image</label>
                    <div class="form-col">
                        <input type="file" name="cover" id="cover">
                    </div>
                </div>
            </div>
            <div class="section-volunteers">
                <div class="page-title box-title">
                    <h2 class="title">Add Volunteers</h2>
                </div>
                <div class="search-box">
                    <div class="row">
                        <div class="col-md-4">
                            <input class="typeahead form-control" id="search" type="text" autocomplete="off" placeholder="Search Members">
                        </div>
                    </div>
                </div>
                <div class="volunteer-list">
                    <table class="table table-bordered typeHead-result" >
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="typeHeadResult"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" name="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
@section('page_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    <script type="text/javascript">
      
        var path = "{{ route('admin.events.autocomplete') }}";
        
        $('#search').typeahead(
            {
                name: 'friends',
                displayKey: 'email',
                source: function (query, process) {
                    return $.get(path, {
                        query: query
                    }, function (data) {
                        return process(data);
                    })
                },
                updater: function (item) {
                    $('#typeHeadResult').append(
                        '<tr id="thrRow'+item.user.id+'">'
                        +'<td><input type="hidden" name="volunteers[]" value="'+item.user.id+'">'+item.name+'</td>'
                        +'<td>'+item.user.email+'</td>' 
                        +'<td><a class="btn btn-xs btn-outline-danger btn-remove-typeHead" data-id="'+item.user.id+'">Remove</a></td>'
                        +'</tr>'
                    );       
                }
            }
        );
        $(document).on('click', '.btn-remove-typeHead',function(){
            var id = $(this).data("id");
            $(this).closest('#thrRow'+id).remove();
        });

    </script>
@endsection