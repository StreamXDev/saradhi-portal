<div class="modal fade" id="editMembership" tabindex="-1" aria-labelledby="editMembershipLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        @if($errors->any())
            <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
        @endif
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
                <label class="form-label">Membership Type</label>
                <div class="form-check">
                    <div>
                        <input class="form-check-input" type="radio" name="type" id="type_single" value="single" @disabled($member->membership->type == 'family')  @checked($member->membership->type == 'single')>
                        <label class="form-check-label" for="type_single">
                            Single  
                        </label>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="type" id="type_family" value="family" @checked($member->membership->type == 'family')>
                    <label class="form-check-label" for="type_family">
                        With Family
                    </label>
                </div>
                @if($member->membership->type == 'family') <small>You cannot change the membership type bcause the user has already added family member(s)</small>@endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Save</button>
            </div>
        </form>
      </div>
    </div>
</div>