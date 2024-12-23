@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Add Committee</h1>
    </div>
    <div class="actions">
        
    </div>
</div>
<div class="page-content">
    <div class="row">
        <div class="col-md-6">
            <div class="form-container">
                <form action="">
                    <div class="form-group row">
                        <div class="col">
                            <label for="committee_type" class="form-label">Committee Type</label>
                            <select name="committee_type" id="committee_type" class="form-select">
                                <option value="">Select</option>
                                @foreach ($committee_types as $committee_type)
                                    <option value="{{$committee_type->id}}" data-category="{{$committee_type->category}}">{{$committee_type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <div id="unitContainer">
                                <label for="unit" class="form-label">Unit</label>
                                <select name="unit" id="unit" class="form-select">
                                    <option value="">Select</option>
                                    @foreach ($units as $unit)
                                        <option value="{{$unit->id}}">{{$unit->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" id="item">
                        <div class="col-md-4">
                            <label for="" class="form-label">Designation</label>
                            <select name="title" class="form-select">
                                <option value="">Select</option>
                                @foreach ($designations as $designation)
                                <option value="{{$designation->id}}">{{$designation->nae}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="" class="form-label">Select Member</label>
                            <input class="typeahead form-control" id="search" type="text" autocomplete="off" placeholder="Search Members">
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
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
@section('page_scripts')
<script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    $(document).ready(function(){
        $('#unitContainer').hide();
        $('#committee_type').on('change', function(){
            var category = $(this).find(':selected').data('category');
            showUnit(category);
        });
    });

    function showUnit(category){
        if(category == 'unit'){
            $('#unitContainer').show();
        }else{
            $('#unitContainer').hide();
        }
    }

    var path = "{{ route('admin.committee.autocomplete') }}";
        
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