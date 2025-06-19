<div class="modal fade" id="editIntroducer" tabindex="-1" aria-labelledby="editIntroducerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route('admin.member.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $member->user->id }}">
            <input type="hidden" name="edit_introducer" value="true">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editIntroducerLabel">Edit Introducer Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="introducer_name" class="form-label">Name <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="introducer_name" id="introducer_name" value="{{ $member->membership->introducer_name }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="introducer_phone" class="form-label">Phone <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="tel" name="introducer_phone" id="introducer_phone" value="{{ $member->membership->introducer_phone }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="introducer_mid" class="form-label">MID <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="text" name="introducer_mid" id="introducer_mid" value="{{ $member->membership->introducer_mid }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="introducer_unit" class="form-label">Unit <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <select name="introducer_unit" id="introducer_unit" class="form-select @error('introducer_unit') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->name }}" @selected( $unit->name == $member->membership->introducer_unit )>{{ $unit->name }}</option>
                                @endforeach
                            </select>
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