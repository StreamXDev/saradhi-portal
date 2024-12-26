@extends('layouts.admin')


@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Create Committee</h1>
    </div>
    <div class="actions">
        
    </div>
</div>
<div class="page-content">
    <div class="row">
        <div class="col-md-6">
            <div class="form-container">
                <form action="{{ route('admin.committee.create') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <div class="col">
                            <label for="committee_type_id" class="form-label">Committee Type</label>
                            <select name="committee_type_id" id="committee_type_id" class="form-select">
                                <option value="">Select</option>
                                @foreach ($committee_types as $committee_type)
                                    <option value="{{$committee_type->id}}" data-category="{{$committee_type->category}}">{{$committee_type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <div id="unitContainer">
                                <label for="member_unit_id" class="form-label">Unit</label>
                                <select name="member_unit_id" id="member_unit_id" class="form-select">
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
                            <label for="formed_on" class="form-label">Starting from</label>
                            <input type="date" name="formed_on" id="formed_on" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script>
    $(document).ready(function(){
        $('#unitContainer').hide();
        $('#committee_type_id').on('change', function(){
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

    
</script>
@endsection