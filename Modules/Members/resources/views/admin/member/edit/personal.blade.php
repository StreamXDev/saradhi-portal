<div class="modal fade" id="editPersonal" tabindex="-1" aria-labelledby="editPersonalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        @if($errors->any())
            <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
        @endif
        <form action="{{ route('admin.member.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $member->user->id }}">
            <input type="hidden" name="edit_personal" value="true">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editPersonalLabel">Edit Personal Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="gender" class="form-label">Gender<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="male" @selected($member->gender == 'male')>Male</option>
                                <option value="female" @selected($member->gender =='female')>Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="blood_group" class="form-label">Blood Group <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($blood_groups as $blood_group)
                                    <option value="{{ $blood_group->name }}" @selected($blood_group->name == $member->blood_group)>{{ $blood_group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="dob" class="form-label">Date of Birth<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ $member->details->dob }}">
                        </div>
                    </div>
                </div>
                <div class="form-title-divider"></div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="passport_no" class="form-label">Passport Number<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="passport_no" id="passport_no" class="form-control @error('passport_no') is-invalid @enderror" value="{{ $member->details->passport_no }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="passport_expiry" class="form-label">Passport Expiry<span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="date" name="passport_expiry" id="passport_expiry" class="form-control @error('passport_expiry') is-invalid @enderror" value="{{ $member->details->passport_expiry }}">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="photo_passport_front" class="form-label">Update passport copy (Front)</label>
                        <div class="control-col">
                            <input  type="file"  name="photo_passport_front"  id="photo_passport_front" class="form-control @error('photo_passport_front') is-invalid @enderror">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="photo_passport_back" class="form-label">Update passport copy (Back)</label>
                        <div class="control-col">
                            <input  type="file"  name="photo_passport_back"  id="photo_passport_back" class="form-control @error('photo_passport_back') is-invalid @enderror">
                        </div>
                    </div>
                </div>
                <div class="form-title-divider"></div>
                <div class="form-group row">
                    <div class="col-md-6 col-lg-4">
                        <label for="profession" class="form-label">Profession</label>
                        <div class="control-col">
                            <input type="text" name="profession" id="profession" class=" form-control" value="{{ $member->details->profession }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label for="company" class="form-label">Company</label>
                        <div class="control-col">
                            <input type="text" name="company" id="company" class=" form-control" value="{{ $member->details->company }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label for="company_address" class="form-label">Company Address</label>
                        <div class="control-col">
                            <input type="text" name="company_address" id="company_address" class=" form-control" value="{{ $member->details->company_address }}">
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