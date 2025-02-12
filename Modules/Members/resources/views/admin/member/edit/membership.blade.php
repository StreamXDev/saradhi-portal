<div class="modal fade" id="editMembership" tabindex="-1" aria-labelledby="editMembershipLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route('admin.member.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $member->user->id }}">
            <input type="hidden" name="edit_membership" value="true">
            <input type="hidden" name="current_type" value="{{$member->membership->type}}">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editMembershipLabel">Edit Membership Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($member->membership->mid)
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="mid" class="form-label">Membership ID</label>
                        <div class="control-col">
                            <input type="text" id="mid" name="mid" value="{{ $member->membership->mid }}" class="form-control">
                        </div>
                    </div>
                </div>
                @endif
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="dob" class="form-label">Expiry Date</label>
                        <div class="control-col">
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ $member->membership->expiry_date }}">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="status">Status</label>
                        <div class="control-col">
                            <select name="status" id="status" class="form-select">
                                <option value="active" @selected($member->membership->status == 'active')>Active</option>
                                <option value="dormant" @selected($member->membership->status == 'dormant')>Dormant</option>
                                <option value="expired" @selected($member->membership->status == 'expired')>Expired</option>
                                <option value="suspended" @selected($member->membership->status == 'suspended')>Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label class="form-label">With Family</label>
                        <div class="control-col">
                            <select name="family_in" id="family_in" class="form-select">
                                <option value="kuwait" @selected($member->membership->family_in == 'kuwait')>Yes</option>
                                <option value="india" @selected($member->membership->family_in == 'india')>No</option>
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