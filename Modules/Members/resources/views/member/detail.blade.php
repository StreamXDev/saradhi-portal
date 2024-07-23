@extends('members::layouts.master')

@section('content')
<div class="container">
    <div class="page-title">
        <h1 class="title">Add Member Details</h1>
        <div class="sub-ttile">Please add your personal information to complete the membership request</div>
    </div>
    @if($errors->any())
        <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif
    <form action="{{ route('member.detail') }}" method="post">
        @csrf
        
        <div class="form-group row">
            <div class="col-md-6">
                <label for="phone" class="control-label">Phone</label>
                <div class="control-col">
                    <input type="tel" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="civil_id" class="control-label">Civil ID</label>
                <div class="control-col">
                    <input type="tel" name="civil_id" id="civil_id" class="form-control">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="dob" class="control-label">Date of Birth</label>
                <div class="control-col">
                    <input type="tel" name="dob" id="dob" class="date form-control">
                </div>
            </div>
            <div class="col-md-6">
                <label for="gender" class="control-label">Gender</label>
                <div class="control-col">
                    <select name="gender" id="gender" class="form-control">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
            
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="passport_no" class="control-label">Passport Number</label>
                <div class="control-col">
                    <input type="tel" name="passport_no" id="passport_no" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <label for="passport_expiry" class="control-label">Passport Expiry</label>
                <div class="control-col">
                    <input type="tel" name="passport_expiry" id="passport_expiry" class="date form-control">
                </div>
            </div>
        </div>
        
        <div class="form-group row">
            
            <div class="col-md-6">
                <label for="blood_group" class="control-label">Blood Group</label>
                <div class="control-col">
                    <select name="blood_group" id="blood_group" class="form-control">
                        <option value="">Select</option>
                        @foreach ($blood_groups as $blood_group)
                            <option value="{{ $blood_group->name }}">{{ $blood_group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="company" class="control-label">Company</label>
                <div class="control-col">
                    <input type="tel" name="company" id="company" class=" form-control">
                </div>
            </div>
            <div class="col-md-6">
                <label for="profession" class="control-label">Profession</label>
                <div class="control-col">
                    <input type="tel" name="profession" id="profession" class=" form-control">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="unit" class="control-label">Saradhi Unit</label>
            <div class="control-col">
                <select name="member_unit_id" id="unit" class="form-control">
                    <option value="">Select</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
@section('page_scripts')
<script type="text/javascript">
    $('.date').datepicker({  
       format: 'yyyy-mm-dd'
     });  
</script> 
@endsection
@endsection