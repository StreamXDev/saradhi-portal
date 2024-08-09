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
    <form action="{{ route('member.detail') }}" method="post" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group row">
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="tel" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="dob" class="form-label">Date of Birth<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="tel" name="dob" id="dob" class="date form-control" value="{{ old('dob') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="gender" class="form-label">Gender<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="gender" id="gender" class="form-select">
                        <option value="male" @selected(old('gender') == 'male')>Male</option>
                        <option value="female" @selected(old('gender') == 'female')>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label for="blood_group" class="form-label">Blood Group</label>
                <div class="control-col">
                    <select name="blood_group" id="blood_group" class="form-select">
                        <option value="">Select</option>
                        @foreach ($blood_groups as $blood_group)
                            <option value="{{ $blood_group->name }}" @selected(old('blood_group') == $blood_group->name)>{{ $blood_group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label class="form-label" for="avatar">Profile Photo</label>
                <div class="control-col">
                    <input  type="file"  name="avatar"  id="avatar" class="form-control @error('avatar') is-invalid @enderror">
                    <div id="avatar" class="form-text">
                        Upload profile photo
                    </div>
                </div>
            </div>
        </div>
        <div class="form-title-divider"></div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="civil_id" class="form-label">Civil ID<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="tel" name="civil_id" id="civil_id" class="form-control" value="{{ old('civil_id') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label class="form-label" for="photo_civil_id_front">Civil Id (Front side):</label>
                <div class="control-col">
                    <input  type="file"  name="photo_civil_id_front"  id="photo_civil_id_front" class="form-control @error('photo_civil_id_front') is-invalid @enderror">
                    <div id="photo_civil_id_front" class="form-text">
                        Upload Civil id front side image
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="photo_civil_id_back">Civil Id (Back side):</label>
                <div class="control-col">
                    <input  type="file"  name="photo_civil_id_back"  id="photo_civil_id_back" class="form-control @error('photo_civil_id_back') is-invalid @enderror">
                    <div id="photo_civil_id_back" class="form-text">
                        Upload Civil id back side image
                    </div>
                </div>
            </div>
        </div>
        <div class="form-title-divider"></div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="passport_no" class="form-label">Passport Number<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="tel" name="passport_no" id="passport_no" class="form-control" value="{{ old('passport_no') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="passport_expiry" class="form-label">Passport Expiry<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="tel" name="passport_expiry" id="passport_expiry" class="date form-control" value="{{ old('passport_expiry') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label class="form-label" for="photo_passport_front">Passport (Front side):</label>
                <div class="control-col">
                    <input  type="file"  name="photo_passport_front"  id="photo_passport_front" class="form-control @error('photo_passport_front') is-invalid @enderror">
                    <div id="photo_passport_front" class="form-text">
                        Upload Passport front side image
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="photo_passport_back">Passport (Back side):</label>
                <div class="control-col">
                    <input  type="file"  name="photo_passport_back"  id="photo_passport_back" class="form-control @error('photo_passport_back') is-invalid @enderror">
                    <div id="photo_passport_back" class="form-text">
                        Upload Passport back side image
                    </div>
                </div>
            </div>
        </div>
        <div class="form-title-divider"></div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="company" class="form-label">Company</label>
                <div class="control-col">
                    <input type="tel" name="company" id="company" class=" form-control" value="{{ old('company') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="profession" class="form-label">Profession</label>
                <div class="control-col">
                    <input type="tel" name="profession" id="profession" class=" form-control" value="{{ old('profession') }}">
                </div>
            </div>
        </div>
        <div class="form-title-divider"></div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="type" class="form-label">Membership Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="single" @selected(old('type') == 'single')>Single</option>
                    <option value="family" @selected(old('type') == 'family')>Family</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="unit" class="form-label">Your Saradhi Unit</label>
                <div class="control-col">
                    <select name="member_unit_id" id="unit" class="form-select">
                        <option value="">Select</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('member_unit_id') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-title-divider"></div>
        <h5 class="form-subtitle">Introducer Details</h5>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="intro_name" class="form-label">Introducer Name<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="introducer_name" id="intro_name" class="form-control"> 
                </div>
            </div>
            <div class="col-md-6">
                <label for="introducer_phone" class="form-label">Introducer's Phone<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="introducer_phone" id="introducer_phone" class="form-control"> 
                </div>
            </div>
            
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="introducer_mid" class="form-label">Introducer's Membership Number</label>
                <div class="control-col">
                    <input type="text" name="introducer_mid" id="introducer_mid" class="form-control"> 
                </div>
            </div>
            <div class="col-md-6">
                <label for="introducer_units" class="form-label">Introducer's Unit</label>
                <div class="control-col">
                    <select name="introducer_unit" id="introducer_unit" class="form-select">
                        <option value="">Select</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->name }}" @selected(old('introducer_unit') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
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