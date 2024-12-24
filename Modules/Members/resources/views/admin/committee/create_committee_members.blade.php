@extends('layouts.admin')


@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Add Committee Members</h1>
    </div>
    <div class="actions">
        
    </div>
</div>
<div class="page-content">
    <div class="row">
        <div class="col-md-6">
            <div class="form-container">
                <form action="{{ route('admin.committee.create.member') }}" method="POST">
                    @csrf
                    <input type="hidden" name="committee_id" value="{{$committee->id}}" >
                    <div class="form-group row" id="item">
                        <div class="col-md-4">
                            <label for="designation_title" class="form-label">Designation</label>
                            <select name="title" id="designation_title" class="form-select">
                                <option value="">Select</option>
                                @foreach ($designations as $designation)
                                    <option value="{{$designation->id}}" data-title="{{$designation->name}}">{{$designation->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="" class="form-label">Select Member</label>
                            <input class="typeahead form-control" id="search" type="text" autocomplete="off" placeholder="Search Name">
                        </div>
                    </div>
                    <div class="volunteer-list">
                        <table class="table table-bordered typeHead-result" >
                            <thead>
                                <tr>
                                    <th>Designation</th>
                                    <th>Member</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="typeHeadResult"></tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <div><strong>{{$committee->committee_type->name}}</strong></div>
                @if($committee->unit)<div><span class="label">Unit: </span><span>{{$committee->unit->name}}</span></div>@endif
                <div><span class="label">Formed On: </span><span>{{$committee->formed_on}}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script>
    $(document).ready(function(){
        

        var designation_title = $('#designation_title').find(':selected').data('title');
        var designation_id = $('#designation_title').find(':selected').val();
        toggleSearchInput(designation_id);
        $('#designation_title').on('change', function(){
            designation_title = $(this).find(':selected').data('title');
            designation_id = $(this).find(':selected').val();
            $('#search').prop('disabled', false);
            toggleSearchInput(designation_id);
        });

        var path = "{{ route('admin.committee.autocomplete') }}";   
        $('#search').typeahead( {
            name: 'best-pictures',
            displayKey: 'value',
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
                    +'<td><input type="hidden" name="designation[]" class="form-control" value="'+designation_id+'">'+designation_title+'</td>'
                    +'<td><input type="hidden" name="members[]" value="'+item.user.id+'">'
                        +'<div class="profile-pill"><div class="details"><div class="title">'+item.user.name+'</div><div>'+item.user.email+'</div><div>MID: '+item.membership.mid+'</div></div></div>'
                    +'</td>' 
                    +'<td><a class="btn btn-xs btn-outline-danger btn-remove-typeHead" data-id="'+item.user.id+'">Remove</a></td>'
                    +'</tr>'
                );       
            }
        });
        $(document).on('click', '.btn-remove-typeHead',function(){
            var id = $(this).data("id");
            $(this).closest('#thrRow'+id).remove();
        });
    });

    function toggleSearchInput(handle){
        if(handle == ''){
            $('#search').prop('disabled', true);
        }
    }

    
</script>
@endsection