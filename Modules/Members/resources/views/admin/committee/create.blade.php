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
                                <option value="{{$committee_type->id}}">{{$committee_type->name}}</option>
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
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
@section('page_scripts')