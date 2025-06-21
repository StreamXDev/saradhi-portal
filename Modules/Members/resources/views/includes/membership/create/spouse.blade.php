<div id="family_details" class="form-family-details">
    <h5 id="mem_type_info" class="mb-3">Enter spouse details</h5>
    <div class="form-group row">
        <div class="col-md-6 col-lg-4">
            <label for="spouse_name" class="form-label">Spouse Name <span class="asterisk">*</span></label>
            <div class="control-col">
                <input type="text" class="form-control @error('spouse_name') is-invalid @enderror" name="spouse_name" id="spouse_name" value="{{ old('spouse_name') }}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_email" class="form-label">Spouse Email <span class="asterisk">*</span></label>
            <div class="control-col">
                <input type="email" class="form-control @error('spouse_email') is-invalid @enderror" name="spouse_email" id="spouse_email" value="{{ old('spouse_email') }}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_phone" class="form-label">Spouse Phone <span class="asterisk">*</span></label>
            <div class="control-col">
                <select name="spouse_tel_country_code" id="spouse_tel_contry_code" class="form-select country-code">
                    @foreach ($countries as $country)
                        <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                    @endforeach
                </select>
                <input type="tel" class="form-control @error('spouse_phone') is-invalid @enderror" name="spouse_phone" id="spouse_phone" value="{{ old('spouse_phone') }}">
                @error('sopuse_phone') <small>{{ $errors->first('spouse_phone') }}</small> @enderror
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_whatsapp" class="form-label">Spouse Whatsapp <span class="asterisk">*</span></label>
            <div class="control-col">
                <select name="spouse_whatsapp_country_code" id="spouse_whatsapp_contry_code" class="form-select country-code">
                    @foreach ($countries as $country)
                        <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                    @endforeach
                </select>
                <input type="tel" class="form-control @error('spouse_whatsapp') is-invalid @enderror" name="spouse_whatsapp" id="spouse_whatsapp" value="{{ old('spouse_whatsapp') }}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_emergency_phone" class="form-label">Spouse Emergency Contact No.<span class="asterisk">*</span></label>
            <div class="control-col">
                <select name="spouse_emergency_country_code" id="spouse_emergency_contry_code" class="form-select country-code">
                    @foreach ($countries as $country)
                        <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                    @endforeach
                </select>
                <input type="tel" name="spouse_emergency_phone" id="spouse_emergency_phone" class="form-control @error('spouse_emergency_phone') is-invalid @enderror" value="{{ old('spouse_emergency_phone') }}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_dob" class="form-label">Spouse Date of Birth<span class="asterisk">*</span></label>
            <div class="control-col">
                <input type="date" name="spouse_dob" id="spouse_dob" class="form-control @error('spouse_dob') is-invalid @enderror" value="{{ old('spouse_dob') }}">
            </div>
        </div>
    
        <div class="col-md-6 col-lg-4">
            <label for="spouse_gender" class="form-label">Spouse Gender<span class="asterisk">*</span></label>
            <div class="control-col">
                <select name="spouse_gender" id="spouse_gender" class="form-select @error('spouse_gender') is-invalid @enderror">
                    <option value="female" @selected(old('spouse_gender') == 'female')>Female</option>
                    <option value="male" @selected(old('spouse_gender') == 'male')>Male</option>
                </select>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_blood_group" class="form-label">Spouse Blood Group <span class="asterisk">*</span></label>
            <div class="control-col">
                <select name="spouse_blood_group" id="spouse_blood_group" class="form-select @error('spouse_blood_group') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach ($blood_groups as $blood_group)
                        <option value="{{ $blood_group->name }}" @selected(old('spouse_blood_group') == $blood_group->name)>{{ $blood_group->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_civil_id" class="form-label">Spouse Civil ID<span class="asterisk">*</span></label>
            <div class="control-col">
                <input type="text" name="spouse_civil_id" id="spouse_civil_id" class="form-control @error('spouse_civil_id') is-invalid @enderror" value="{{ old('spouse_civil_id') }}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_paci" class="form-label">Spouse PACI No.</label>
            <div class="control-col">
                <input type="text" name="spouse_paci" id="spouse_paci" class="form-control @error('spouse_paci') is-invalid @enderror" value="{{ old('spouse_paci') }}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_passport_no" class="form-label">Spouse Passport Number <span class="asterisk">*</span></label>
            <div class="control-col">
                <input type="text" name="spouse_passport_no" id="spouse_passport_no" class="form-control @error('spouse_passport_no') is-invalid @enderror" value="{{ old('spouse_passport_no' )}}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_passport_expiry" class="form-label">Spouse Passport Expiry<span class="asterisk">*</span></label>
            <div class="control-col">
                <input type="date" name="spouse_passport_expiry" id="spouse_passport_expiry" class="form-control @error('spouse_passport_expiry') is-invalid @enderror" value="{{ old('spouse_passport_expiry' )}}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="spouse_avatar">Spouse Profile Photo <span class="asterisk">*</span></label>
            <div class="control-col">
                <input  type="file"  name="spouse_avatar"  id="spouse_avatar" class="form-control @error('spouse_avatar') is-invalid @enderror">
                <div class="form-text">
                    Upload profile photo
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="spouse_photo_civil_id_front">Civil ID Copy (Front) <span class="asterisk"> *</span></label>
            <div class="control-col">
                <input  type="file"  name="spouse_photo_civil_id_front"  id="spouse_photo_civil_id_front" class="form-control @error('spouse_photo_civil_id_front') is-invalid @enderror">
                <div class="form-text">
                    Upload self attested copy of front or front & back
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="spouse_photo_civil_id_back">Civil ID Copy (Back) <span class="asterisk">*</span></label>
            <div class="control-col">
                <input  type="file"  name="spouse_photo_civil_id_back"  id="spouse_photo_civil_id_back" class="form-control @error('spouse_photo_civil_id_back') is-invalid @enderror">
                <div class="form-text">
                    Upload self attested copy
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="spouse_photo_passport_front">Passport Copy (Front) <span class="asterisk">*</span></label>
            <div class="control-col">
                <input  type="file"  name="spouse_photo_passport_front"  id="spouse_photo_passport_front" class="form-control @error('spouse_photo_passport_front') is-invalid @enderror">
                <div  class="form-text">
                    Upload self attested copy of front or front and back
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-label" for="spouse_photo_passport_back">Passport Copy (Back) <span class="asterisk"> *</span></label>
            <div class="control-col">
                <input  type="file"  name="spouse_photo_passport_back"  id="spouse_photo_passport_back" class="form-control @error('spouse_photo_passport_back') is-invalid @enderror">
                <div class="form-text">
                    Upload self attested copy
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_company" class="form-label">Spouse Company</label>
            <div class="control-col">
                <input type="text" name="spouse_company" id="spouse_company" class="form-control" value="{{ old('spouse_company' )}}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_profession" class="form-label">Spouse Professsion</label>
            <div class="control-col">
                <input type="text" name="spouse_profession" id="spouse_profession" class="form-control" value="{{ old('spouse_profession' )}}">
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="spouse_company_address" class="form-label">Spouse Company Address</label>
            <div class="control-col">
                <input type="text" name="spouse_company_address" id="spouse_company_address" class="form-control" value="{{ old('spouse_company_address' )}}">
            </div>
        </div>
    </div>
    
</div>
