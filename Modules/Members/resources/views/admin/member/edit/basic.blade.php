<div class="modal fade" id="editBasics" tabindex="-1" aria-labelledby="editBasicsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        @if($errors->any())
            <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
        @endif
        <form action="{{ route('admin.member.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $member->user->id }}">
            <input type="hidden" name="edit_basic" value="true">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editBasicsLabel">Edit Basic Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="name" class="form-label">Name <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="name" id="name" value="{{ $member->name }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="phone" class="form-label">Phone<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="tel_country_code" id="tel_contry_code" class="form-select country-code">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->calling_code }}" @selected( $country->calling_code == $member->user->calling_code)>{{ $country->name }} (+{{ $country->calling_code}})</option>
                                @endforeach
                            </select>
                            <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $member->user->phone }}">
                        </div>
                    </div>
                    
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="whatsapp" class="form-label">Whatsapp<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="whatsapp_country_code" id="whatsapp_contry_code" class="form-select country-code">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->calling_code }}" @selected( $country->calling_code == $member->details->whatsapp_code )>{{ $country->name }} (+{{ $country->calling_code}})</option>
                                @endforeach
                            </select>
                            <input type="tel" name="whatsapp" id="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ $member->details->whatsapp }}">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="emergency_phone" class="form-label">Emergency Contact No in Kuwait<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="emergency_country_code" id="emergency_contry_code" class="form-select country-code">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->calling_code }}" @selected($country->calling_code == $member->details->emergency_phone_code )>{{ $country->name }} (+{{ $country->calling_code}})</option>
                                @endforeach
                            </select>
                            <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ $member->details->emergency_phone }}">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="unit" class="form-label">Unit <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="member_unit_id" id="unit" class="form-select @error('member_unit_id') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected( $unit->id == $member->details->member_unit_id )>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="paci" class="form-label">PACI No.</label>
                        <div class="control-col">
                            <input type="text" name="paci" id="paci" class="form-control" value="{{ $member->details->paci }}">
                        </div>
                    </div>
                </div>
                <div class="form-title-divider"></div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="civil_id" class="form-label">Civil ID<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="civil_id" id="civil_id" class="form-control @error('civil_id') is-invalid @enderror" value="{{ $member->details->civil_id }}">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="civil_id_front" class="form-label">Update Civil ID Copy (Front)</label>
                        <div class="control-col">
                            <input  type="file" name="photo_civil_id_front"  id="photo_civil_id_front" class="form-control @error('photo_civil_id_front') is-invalid @enderror" >
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="civil_id_back" class="form-label">Update Civil ID Copy (Back)</label>
                        <div class="control-col">
                            <input  type="file" name="photo_civil_id_back"  id="photo_civil_id_back" class="form-control @error('photo_civil_id_back') is-invalid @enderror" >
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Save</button>
            </div>
        </form>
      </div>
    </div>
</div>