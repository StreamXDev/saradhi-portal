@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Add Family Member</h1>
    </div>
</div>
<div class="page-content">
    <form action="{{ route('admin.member.family') }}" method="POST" id="registerForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="parent" value="{{ $parent->user_id }}">
        <div class="form-group">
            <div class="col-md-2">
                <label for="profile_type" class="form-label">Type<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="profile_type" id="profile_type" class="form-select">
                        <option value="spouse" @disabled($parent->hasSpouse ? true : false)>Spouse</option>
                        <option value="child">Child</option>
                    </select> 
                    
                </div>
            </div>
        </div>
        <div class="form-section-title">User Information</div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="name" class="form-label">Name<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="email" class="form-label">Email<span class="asterisk spouse-view">*</span></label>
                <div class="control-col block">
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email') <small>{{ $errors->first('email') }}</small> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone<span class="asterisk spouse-view">*</span></label>
                <div class="control-col">
                    <select name="tel_country_code" id="tel_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                </div>
            </div>
        </div>
        <div class="form-group row spouse-view">
            <div class="col-md-4">
                <label for="whatsapp" class="form-label">Whatsapp<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="whatsapp_country_code" id="whatsapp_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="whatsapp" id="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label for="emergency_phone" class="form-label">Emergency Contact No in Kuwait<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="emergency_country_code" id="emergency_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone') ? old('emergency_phone') : $parent->details->emergency_phone }}">
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
                    <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="gender" class="form-label">Gender<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="male" @selected(old('gender') == 'male')>Male</option>
                        <option value="female" @selected(old('gender') == 'female')>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <label for="blood_group" class="form-label">Blood Group <span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                        <option value="">Select</option>
                        @foreach ($blood_groups as $blood_group)
                            <option value="{{ $blood_group->name }}" @selected(old('blood_group') == $blood_group->name)>{{ $blood_group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="civil_id" class="form-label">Civil ID<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="civil_id" minlength="12" maxlength="12" id="civil_id" class="form-control @error('civil_id') is-invalid @enderror" value="{{ old('civil_id') }}">
                </div>
            </div>
            <div class="col-md-2 spouse-view">
                <label for="paci" class="form-label">PACI No.</label>
                <div class="control-col">
                    <input type="text" name="paci" id="paci" class="form-control" value="{{ old('paci') }}">
                </div>
            </div>
        </div>
        <div class="form-section-title">Passport Details</div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="passport_no" class="form-label">Passport Number<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="passport_no" id="passport_no" class="form-control @error('passport_no') is-invalid @enderror" value="{{ old('passport_no') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="passport_expiry" class="form-label">Passport Expiry<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="date" name="passport_expiry" id="passport_expiry" class="form-control @error('passport_expiry') is-invalid @enderror" value="{{ old('passport_expiry') }}">
                </div>
            </div>
        </div>
        <div class="form-section-title spouse-view">Professional Details</div>
        <div class="form-group row spouse-view">
            <div class="col-md-4">
                <label for="profession" class="form-label">Profession</label>
                <div class="control-col">
                    <input type="text" name="profession" id="profession" class=" form-control" value="{{ old('profession') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label for="company" class="form-label">Company</label>
                <div class="control-col">
                    <input type="text" name="company" id="company" class=" form-control" value="{{ old('company') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label for="company_address" class="form-label">Company Address</label>
                <div class="control-col">
                    <input type="text" name="company_address" id="company_address" class=" form-control" value="{{ old('company_address') }}">
                </div>
            </div>
        </div>
        <div class="form-section-title spouse-view">Address</div>
        <div class="form-section-subtitle spouse-view">Kuwait Address</div>
        <div class="form-group row spouse-view">
            <div class="col-md-2">
                <label for="governorate" class="form-label">Governorate <span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="governorate" id="governorate" class="form-select @error('governorate') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="ahmadi" @selected(old('governorate') == 'ahmadi')>Ahmadi</option>
                        <option value="farvaniya" @selected(old('governorate') == 'farvaniya')>Farvaniya</option>
                        <option value="hawally" @selected(old('governorate') == 'hawally')>Hawally</option>
                        <option value="jahara" @selected(old('governorate') == 'jahara')>Jahara</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <label for="unit" class="form-label">Preferred Unit <span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="member_unit_id" id="unit" class="form-select @error('member_unit_id') is-invalid @enderror">
                        <option value="">Select</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('member_unit_id') == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <label for="local_address_area" class="form-label">Area, Street & Block Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_area" id="local_address_area " class="form-control @error('local_address_area') is-invalid @enderror" value="{{ old('local_address_area') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="local_address_building" class="form-label">Building Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_building" id="local_address_building" class="form-control @error('local_address_building') is-invalid @enderror" value="{{ old('local_address_building') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="local_address_flat" class="form-label">Flat Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_flat" id="local_address_flat" class="form-control @error('local_address_flat') is-invalid @enderror" value="{{ old('local_address_flat') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="local_address_floor" class="form-label">Floor Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_floor" id="local_address_floor" class="form-control @error('local_address_floor') is-invalid @enderror" value="{{ old('local_address_floor') }}">
                </div>
            </div>
        </div>
        <div class="form-title-divider spouse-view"></div>
        <div class="form-section-subtitle spouse-view">India Address</div>
        <div class="form-group row spouse-view">
            <div class="col-md-4">
                <label for="permanent_address_line_1" class="form-label">Address</label>
                <div class="control-col">
                    <textarea name="permanent_address_line_1" id="permanent_address_line_1" rows="1" class="form-control">{{ old('permanent_address_line_1') }}</textarea>
                </div>
            </div>
            <div class="col-md-8 row">
                <div class="col-md-6">
                    <label for="permanent_address_district" class="form-label">District</label>
                    <div class="control-col">
                        <select name="permanent_address_district" id="permanent_address_district" class="form-select">
                            @foreach ($district_kerala as $district)
                                <option value="{{ $district['slug'] }}" @selected(old('permanent_address_district') == $district['slug'])>{{ $district['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="permanent_address_contact" class="form-label">Contact No. in India</label>
                    <div class="control-col">
                        <select name="permanent_address_country_code" id="permanent_address_contry_code" class="form-select country-code">
                            @foreach ($countries as $country)
                                @if($country->code == 'in')
                                    <option value="{{ $country->calling_code }}" >{{ $country->name }} (+{{ $country->calling_code}})</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="tel" name="permanent_address_contact" id="permanent_address_contact" class="form-control" value="{{ old('permanent_address_contact') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section-title spouse-view">Other Details</div>
        <div class="form-group row spouse-view">
            <div class="col-md-2">
                <label for="sndp_branch" class="form-label">SNDP Branch</label>
                <div class="control-col">
                    <input type="text" name="sndp_branch" id="sndp_branch" class="form-control" value="{{ old('sndp_branch') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="sndp_branch_number" class="form-label">Branch Number</label>
                <div class="control-col">
                    <input type="text" name="sndp_branch_number" id="sndp_branch_number" class="form-control" value="{{ old('sndp_branch_number') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="sndp_union" class="form-label">SNDP Union</label>
                <div class="control-col">
                    <input type="text" name="sndp_union" id="sndp_union" class="form-control" value="{{ old('sndp_union') }}">
                </div>
            </div>
        </div>
        <div class="form-section-title spouse-view">Membership</div>
        <div class="form-group row spouse-view">
            <div class="col-md-2">
                <label for="verification" class="form-label">Need Verification Process?</label>
                <div class="control-col">
                    <label for="verification_npo"><input class="form-checkobx" type="radio" name="verification" id="verification_npo" value="no" checked> No</label>&nbsp;&nbsp;
                    <label for="verification_yes"><input class="form-checkobx" type="radio" name="verification" id="verification_yes" value="yes" > Yes</label>
                    <small id="spouseMIDHelp" class="form-text text-muted">If Yes, the request will be sent to verification process. Otherwise you should add the MID now</strong></small>
                </div>
            </div>
            <div class="col-md-2" id="midSpouse">
                <div class="form-group">
                    <label for="mid" class="form-label">MID<span class="asterisk">*</span></label>
                    <div class="control-col">
                        @if($parent->membership->joined_as == 'old')
                            <input type="hidden" name="mid" value="{{ $parent->membership->mid }}">
                            <input type="text"  id="mid" class="form-control" value="{{ old('mid') ? old('mid') : $parent->membership->mid }}" >
                            <small id="spouseMIDHelp" class="form-text text-muted">Suggested MID: <strong>{{ $suggested_mid+1 }}</strong></small>
                        @else
                            <input type="text" name="mid" id="mid" class="form-control @error('mid') is-invalid @enderror" value="{{ old('mid') }}" aria-describedby="spouseMIDHelp">
                            <small id="spouseMIDHelp" class="form-text text-muted">Suggested MID: <strong>{{ $suggested_mid+1 }}</strong></small>
                        @endif
                    </div>
                </div>
                <div>
                    <label for="start_date" class="form-label">Start Date<span class="asterisk">*</span></strong></label>
                    <div class="control-col">
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') ? old('start_date') : date('Y-m-d') }}" >
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group d-flex">
            <button type="submit" name="action" value="submit" class="btn btn-lg btn-primary justify-self-end">Submit Details</button>
        </div>
    </form>
</div>
@endsection
@section('page_scripts')
<script type="text/javascript">
    $(document).ready(function(){
        let profile_type = $('#profile_type').find(":selected").val();
        changeSpouseView(profile_type);
        $('#profile_type').on('change', function(){
            let profile_type = $('#profile_type').find(":selected").val();
            changeSpouseView(profile_type);
        });

        var verificationInput = $("input[name$='verification']");
        var verification = verificationInput.filter(':checked').val() == 'yes' ? true : false;
        $('#midSpouse').hide();
        if(verification){
            handleMID(true);
        }else{
            handleMID(false);
        }

        verificationInput.on('click', function(){
            var v = $(this).val();
            if(v == 'yes'){
                handleMID(true);
            }else{
                handleMID(false);
            }
        });
    });

    function changeSpouseView(profile_type){
        if(profile_type === 'child'){
            $('.spouse-view').hide();
        }else{
            $('.spouse-view').show();
        }
    }

    function handleMID($verification = false){
        if(!$verification){
            $('#midSpouse').show();
        }else{
            $('#midSpouse').hide();
        }
    }
</script> 
@endsection