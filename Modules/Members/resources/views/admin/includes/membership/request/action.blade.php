@if($current_status->request_status->slug == 'approved' && $request_action->action['slug'] == 'confirm')
    @if(Auth::user()->can('membership_request.confirm'))
        <button class="btn btn-success btn-xs" data-bs-toggle="modal" data-bs-target="#requestConfirmModal">Confirm</button>
        <div class="modal fade" id="requestConfirmModal" tabindex="-1" aria-labelledby="requestConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.member.confirm_membership_request') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="requestConfirmModalLabel">Confirm Request</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                            <div class="form-group">
                                <label for="mid" class="control-label">Member Id <span class="asterisk">*</span> (Suggested Member Id: <strong>{{ $suggested_mid }}</strong>)</label>
                                <div class="col"><input type="text" name="mid" id="mid" class="form-control"></div>
                            </div>
                            <div class="form-group">
                                <label for="start_date" class="control-label">Joining Date <span class="asterisk">*</span></label>
                                <div class="col">
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') ? old('start_date') : date('Y-m-d') }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea name="remark" id="remark" rows="5" class="form-control" placeholder="Enter comments"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@else
    <button class="btn btn-xs @if($current_status->rejected == null) btn-success @else btn-warning @endif" data-bs-toggle="modal" data-bs-target="#requestActionModal">{{ $request_action->action['name'] }}</button>
    @if($current_status->rejected) <!-- <button class="btn btn-xs btn-danger" data-bs-toggle="modal" data-bs-target="#memberDeleteModal">Delete Request</button> -->@endif
    <div class="modal fade" id="requestActionModal" tabindex="-1" aria-labelledby="requestActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.member.change_status') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="requestActionModalLabel">{{ $request_action->action['name'] }} Request</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($current_status->rejected != null)
                            <div class="form-group text-danger">The request is already rejected. Do you want to {{ $request_action->action['slug'] }} the action?</div>
                        @else
                            <div class="form-group"><strong>Are you sure want to {{ $request_action->action['slug'] }} the request?</strong></div>
                        @endif
                        <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                        <input type="hidden" name="current_status_id" value="{{ $request_action->request_status_id }}">
                        <input type="hidden" name="action" value="{{ $request_action->action['slug'] }}">
                        <div class="form-group">
                            <textarea name="remark" id="remark" cols="30" rows="5" placeholder="Enter comment (Optional)" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">{{ $request_action->action['name'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
@endif
@if($current_status->rejected == null)
    <button class="btn btn-xs btn-danger" data-bs-toggle="modal" data-bs-target="#requestActionReject">Reject</button>
    <div class="modal fade" id="requestActionReject" tabindex="-1" aria-labelledby="requestActionRejectLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.member.change_status') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="requestActionRejectLabel">Reject Request</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><strong>Are you sure want to Reject the request?</strong></div>
                        <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                        <input type="hidden" name="current_status_id" value="{{ $request_action->request_status_id }}">
                        <input type="hidden" name="action" value="reject">
                        <div class="form-group">
                            <textarea name="remark" id="remark" cols="30" rows="5" placeholder="Enter comment" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger" name="reject">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif


