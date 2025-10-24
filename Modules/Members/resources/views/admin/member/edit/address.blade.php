<div class="modal fade" id="editAddress" tabindex="-1" aria-labelledby="editAddressLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route('admin.member.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $member->user->id }}">
            <input type="hidden" name="edit_address" value="true">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editAddressLabel">Edit Address</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-section-subtitle">Kuwait Address</div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="governorate" class="form-label">Governorate <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="governorate" id="governorate" class="form-control @error('governorate') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="ahmadi" @selected($member->localAddress && $member->localAddress->governorate == 'ahmadi')>Ahmadi</option>
                                <option value="farvaniya" @selected($member->localAddress && $member->localAddress->governorate == 'farvaniya')>Farvaniya</option>
                                <option value="hawally" @selected($member->localAddress && $member->localAddress->governorate == 'hawally')>Hawally</option>
                                <option value="jahara" @selected($member->localAddress && $member->localAddress->governorate == 'jahara')>Jahara</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="local_address_area" class="form-label">Area, Street & Block Number <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="local_address_area" id="local_address_area " class="form-control @error('local_address_area') is-invalid @enderror" value="@isset($member->localAddress){{$member->localAddress->line_1}}@endisset">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="local_address_building" class="form-label">Building Number <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="local_address_building" id="local_address_building" class="form-control @error('local_address_building') is-invalid @enderror" value="@isset($member->localAddress){{$member->localAddress->building}}@endisset">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="local_address_flat" class="form-label">Flat Number <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="local_address_flat" id="local_address_flat" class="form-control @error('local_address_flat') is-invalid @enderror" value="@isset($member->localAddress){{$member->localAddress->flat}}@endisset">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="local_address_floor" class="form-label">Floor Number <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="local_address_floor" id="local_address_floor" class="form-control @error('local_address_floor') is-invalid @enderror" value="@isset($member->localAddress){{ $member->localAddress->floor}}@endisset">
                        </div>
                    </div>
                </div>
                <div class="form-title-divider"></div>
                <div class="form-section-subtitle">India Address</div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="permanent_address_line_1" class="form-label">Address</label>
                        <div class="control-col">
                            <textarea name="permanent_address_line_1" id="permanent_address_line_1" cols="20" rows="4" class="form-control"> @isset($member->permanentAddress->line_1) {{ $member->permanentAddress->line_1 }} @endisset</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="permanent_address_district" class="form-label">District</label>
                            <div class="control-col">
                                <select name="permanent_address_district" id="permanent_address_district" class="form-select">
                                    @foreach ($district_kerala as $district)
                                        <option value="{{ $district['slug'] }}" @isset($member->permanentAddress->district) @selected($member->permanentAddress->district == $district['slug']) @endisset>{{ $district['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="permanent_address_contact" class="form-label">Contact No. in India</label>
                            <div class="control-col">
                                <select name="permanent_address_country_code" id="permanent_address_country_code" class="form-select country-code">
                                    @foreach ($countries as $country)
                                        @if($country->code == 'in')
                                            <option value="{{ $country->calling_code }}" @isset($member->permanentAddress->country) @selected($member->permanentAddress->country == $country->name ) @endisset>{{ $country->name }} (+{{ $country->calling_code}})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="tel" name="permanent_address_contact" id="permanent_address_contact" class="form-control" value="@isset($member->permanentAddress->contact){{ $member->permanentAddress->contact }} @endisset">
                            </div>
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