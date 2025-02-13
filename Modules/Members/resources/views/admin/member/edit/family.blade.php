@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Edit Family Member</h1>
    </div>
</div>
<div class="page-content">
    <form action="{{ route('admin.member.family.update') }}" method="POST" id="registerForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$member->id}}">
        <div class="form-section-title">Chid Information</div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="name" class="form-label">Name<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') ?? $member->name }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="email" class="form-label">Email</label>
                <div class="control-col block">
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') ?? $member->email }}">
                    @error('email') <small>{{ $errors->first('email') }}</small> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone</label>
                <div class="control-col">
                    <select name="tel_country_code" id="tel_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @selected($member->calling_code == $country->calling_code)>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') ?? $member->phone }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="photo" class="form-label">Profile Photo</label>
                <div class="control-col block">
                    <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror" value="{{ old('avatar') }}">
                    @error('avatar') <small>{{ $errors->first('avatar') }}</small> @enderror
                </div>
            </div>
        </div>
        <div class="form-section-title">Personal Details</div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="dob" class="form-label">Date of Birth<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') ?? $member->dob}}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="gender" class="form-label">Gender<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="male" @selected($member->gender == 'male')>Male</option>
                        <option value="female" @selected($member->gender == 'female')>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <label for="blood_group" class="form-label">Blood Group <span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                        <option value="">Select</option>
                        @foreach ($blood_groups as $blood_group)
                            <option value="{{ $blood_group->name }}" @selected($member->blood_group == $blood_group->name)>{{ $blood_group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="civil_id" class="form-label">Civil ID<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="civil_id" minlength="12" maxlength="12" id="civil_id" class="form-control @error('civil_id') is-invalid @enderror" value="{{ old('civil_id') ?? $member->civil_id }}">
                </div>
            </div>
        </div>
        <div class="form-section-title">Passport Details</div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="passport_no" class="form-label">Passport Number<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="passport_no" id="passport_no" class="form-control @error('passport_no') is-invalid @enderror" value="{{ old('passport_no') ?? $member->passport_no }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="passport_expiry" class="form-label">Passport Expiry<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="date" name="passport_expiry" id="passport_expiry" class="form-control @error('passport_expiry') is-invalid @enderror" value="{{ old('passport_expiry') ?? $member->passport_expiry }}">
                </div>
            </div>
        </div>
        
        
        <div class="form-group d-flex">
            <button type="submit" name="action" value="submit" class="btn btn-lg btn-primary justify-self-end">Save</button>
        </div>
    </form>
</div>
@endsection