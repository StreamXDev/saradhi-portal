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
    <div>
        <h1 class="title">Add Invitiees</h1>
        <div class="subtitle">Event: {{$event->title}}</div>
    </div>
    <div>
        <a href="/admin/events/view/{{$event->id}}" class="btn btn-xs btn-outline-primary">View Event</a>
        <a href="/admin/events/{{$event->id}}/volunteers" class="btn btn-xs btn-primary">Volunteers</a>
    </div>
</div>
<div class="page-content">
    <div class="section-title box-title">
        <h2 class="title">Add Volunteer</h2>
    </div>
    <div class="form-container">
        <form action="{{ route('admin.events.volunteer.add') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="event_id" value="{{$event->id}}">
            <div class="section-volunteers">
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
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
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