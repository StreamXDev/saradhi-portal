@extends('members::layouts.app')

@section('content')
<div class="container">
    <div class="page-title">
        <div>
            <h1 class="title">Add Member Details</h1>
            <div class="sub-ttile">Please add your personal information to complete the membership request</div>
        </div>
    </div>
    @if($errors->any())
        <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif
    <form action="{{ route('member.detail') }}" method="post" enctype="multipart/form-data" id="memDetailForm">
        @csrf
        
        <div class="form-section-title">Personal Information</div>

        <div class="form-group row">
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="tel_country_code" id="tel_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="col-md-6">
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
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="emergency_phone" class="form-label">Emergency Contact No in Kuwait<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="emergency_country_code" id="emergency_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="dob" class="form-label">Date of Birth<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="gender" class="form-label">Gender<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="male" @selected(old('gender') == 'male')>Male</option>
                        <option value="female" @selected(old('gender') == 'female')>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
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
            <div class="col-md-6">
                <label class="form-label" for="avatar">Profile Photo <span class="asterisk">(only image file)*</span></label>
                <div class="control-col">
                    <input  type="file"  name="avatar"  id="avatar" class="form-control @error('avatar') is-invalid @enderror">
                    <div class="form-text">
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
                    <input type="text" name="civil_id" id="civil_id" class="form-control @error('civil_id') is-invalid @enderror" value="{{ old('civil_id') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="paci" class="form-label">PACI No.</label>
                <div class="control-col">
                    <input type="text" name="paci" id="paci" class="form-control" value="{{ old('paci') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label class="form-label" for="photo_civil_id_front">Civil ID Copy (Front / Front and Back) <span class="asterisk">(self attested copy) *</span></label>
                <div class="control-col">
                    <input  type="file"  name="photo_civil_id_front"  id="photo_civil_id_front" class="form-control @error('photo_civil_id_front') is-invalid @enderror">
                    <div class="form-text">
                        Upload Civil id front side image
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="photo_civil_id_back">Civil ID Copy (Back) <span class="asterisk">(Upload self attested copy) *</span></label>
                <div class="control-col">
                    <input  type="file"  name="photo_civil_id_back"  id="photo_civil_id_back" class="form-control @error('photo_civil_id_back') is-invalid @enderror">
                    <div class="form-text">
                        Upload Civil id back side image
                    </div>
                </div>
            </div>
        </div>
        

        <div class="form-section-title">Passport Details</div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="passport_no" class="form-label">Passport Number<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="passport_no" id="passport_no" class="form-control @error('passport_no') is-invalid @enderror" value="{{ old('passport_no') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="passport_expiry" class="form-label">Passport Expiry<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="date" name="passport_expiry" id="passport_expiry" class="form-control @error('passport_expiry') is-invalid @enderror" value="{{ old('passport_expiry') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label class="form-label" for="photo_passport_front">Passport Copy (Front / Front and Back) <span class="asterisk">(Upload self attested copy) *</span></label>
                <div class="control-col">
                    <input  type="file"  name="photo_passport_front"  id="photo_passport_front" class="form-control @error('photo_passport_front') is-invalid @enderror">
                    <div id="photo_passport_front" class="form-text">
                        Upload Passport front side image
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="photo_passport_back">Passport Copy (Back) <span class="asterisk">(Upload self attested copy) *</span></label>
                <div class="control-col">
                    <input  type="file"  name="photo_passport_back"  id="photo_passport_back" class="form-control @error('photo_passport_back') is-invalid @enderror">
                    <div id="photo_passport_back" class="form-text">
                        Upload Passport back side image
                    </div>
                </div>
            </div>
        </div>
        <div class="form-section-title">Professional Details</div>
        <div class="form-group row">
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
        <div class="form-section-title">Membership Details</div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="type" id="type_single" value="single"  @checked(old('type') == 'single')>
            <label class="form-check-label" for="type_single">
              Single
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="type" id="type_family" value="family" @checked(old('type') == 'family')>
            <label class="form-check-label" for="type_family">
              With Family
            </label>
        </div>
        <br />

        @include('members::includes.membership.create.spouse')

        <div class="form-section-title">Address</div>
        <div class="form-section-subtitle">Kuwait Address</div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="governorate" class="form-label">Governorate <span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="governorate" id="governorate" class="form-control @error('governorate') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="ahmadi" @selected(old('governorate') == 'ahmadi')>Ahmadi</option>
                        <option value="farvaniya" @selected(old('governorate') == 'farvaniya')>Farvaniya</option>
                        <option value="hawally" @selected(old('governorate') == 'hawally')>Hawally</option>
                        <option value="jahara" @selected(old('governorate') == 'jahara')>Jahara</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
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
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="local_address_area" class="form-label">Area, Street & Block Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_area" id="local_address_area " class="form-control @error('local_address_area') is-invalid @enderror" value="{{ old('local_address_area') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="local_address_building" class="form-label">Building Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_building" id="local_address_building" class="form-control @error('local_address_building') is-invalid @enderror" value="{{ old('local_address_building') }}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="local_address_flat" class="form-label">Flat Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_flat" id="local_address_flat" class="form-control @error('local_address_flat') is-invalid @enderror" value="{{ old('local_address_flat') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="local_address_floor" class="form-label">Floor Number <span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="local_address_floor" id="local_address_floor" class="form-control @error('local_address_floor') is-invalid @enderror" value="{{ old('local_address_floor') }}">
                </div>
            </div>
        </div>
        <div class="form-title-divider"></div>
        <div class="form-section-subtitle">India Address</div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="permanent_address_line_1" class="form-label">Address</label>
                <div class="control-col">
                    <textarea name="permanent_address_line_1" id="permanent_address_line_1" cols="20" rows="4" class="form-control">{{ old('permanent_address_line_1') }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="permanent_address_district" class="form-label">District</label>
                    <div class="control-col">
                        <select name="permanent_address_district" id="permanent_address_district" class="form-select">
                            @foreach ($district_kerala as $district)
                                <option value="{{ $district['slug'] }}" @selected(old('permanent_address_district') == $district['slug'])>{{ $district['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
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

        <div class="form-section-title">Other Details</div>
        <div class="form-group row">
            <div class="col-md-6 col-lg-4">
                <label for="sndp_branch" class="form-label">SNDP Branch</label>
                <div class="control-col">
                    <input type="text" name="sndp_branch" id="sndp_branch" class="form-control" value="{{ old('sndp_branch') }}">
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="sndp_branch_number" class="form-label">Branch Number</label>
                <div class="control-col">
                    <input type="text" name="sndp_branch_number" id="sndp_branch_number" class="form-control" value="{{ old('sndp_branch_number') }}">
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="sndp_union" class="form-label">SNDP Union</label>
                <div class="control-col">
                    <input type="text" name="sndp_union" id="sndp_union" class="form-control" value="{{ old('sndp_union') }}">
                </div>
            </div>
        </div>

        <div class="form-section-title">Reference Details (If any)</div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="intro_name" class="form-label">Introducer Name<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="introducer_name" id="intro_name" class="form-control" value="{{ old('introducer_name') }}"> 
                </div>
            </div>
            <div class="col-md-6">
                <label for="introducer_phone" class="form-label">Introducer's Phone<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="introducer_country_code" id="contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="text" name="introducer_phone" id="introducer_phone" class="form-control" value="{{ old('introducer_phone') }}"> 
                </div>
            </div>
            
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="introducer_mid" class="form-label">Introducer's Membership Number</label>
                <div class="control-col">
                    <input type="text" name="introducer_mid" id="introducer_mid" class="form-control" value="{{ old('introducer_mid') }}"> 
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
            <button type="submit" name="action" value="submit" class="btn btn-primary">Save & Submit</button>
        </div>
    </form>
</div>
@endsection
@section('page_scripts')
<script type="text/javascript">
    $('.date').datepicker({  
       format: 'yyyy-mm-dd'
     });  

     $(document).ready(function(){
        
        var input  = $("input[name$='type']");
        var type;
        if(input.is(':checked')){
            if(input.filter(':checked').val() == 'family'){
                $('#family_details').show();
            }
        }

        input.on('click', function(){
            type = $(this).val();
            if(type == 'family'){
                $('#family_details').show();
            }else{
                $('#family_details').hide();
            }
        });

        $("input[type$=file]").on('change', function(){
            $(this).next('.form-text').removeClass('error');
            var imageKb = this.files[0].size/1024;
            var imageMb = imageKb / 1024;
            if(imageMb > 2){
                $(this).addClass('is-invalid').val('').next('.form-text').addClass('error').text('The file should be less than 2MB');
            }
        })

    })
</script> 
@endsection