<div class="modal fade" id="editEmail_{{$member->id}}" tabindex="-1" aria-labelledby="editEmail_{{$member->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        
        <form action="{{ route('admin.member.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ $member->user->id }}">
            <input type="hidden" name="edit_email" value="true">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editEmail_{{$member->id}}Label">Edit Email</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="name" class="form-label">Current Email </label>
                        <div class="control-col">
                            {{ $member->user->email }}
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="email" class="form-label">New Email <span class="asterisk">*</span></label>
                        <div class="control-col">
                            <input type="email" name="email" id="email" value="{{ $member->user->email }}" class="form-control">
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