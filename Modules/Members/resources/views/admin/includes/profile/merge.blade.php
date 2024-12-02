<div class="modal fade" id="mergeMembers" tabindex="-1" aria-labelledby="mergeMembersLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.member.merge') }}" method="POST" onSubmit="if(!confirm('Are you sure want to merge?')){return false;}">
                @csrf
                <input type="hidden" name="new_id" value="{{$member->user->id}}">
                <input type="hidden" name="old_id" value="{{$duplicate->user->id}}">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="mergeMembersLabel">Merge Members</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="merge-title"><strong>Alert: The following data will be copied from this membership request to existing member's details</strong></div>
                    
                    <div class="merge-item-view-wrapper">
                        <div class="merge-item-view">
                            <div class="item">
                                <div class="title">New Member</div>
                                <div class="image">
                                    @if($member->user->avatar)
                                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="" class="list-profile-photo" />
                                    @endif
                                </div>
                                <div class="details">
                                    <div class="name">{{ $member->user->name}}</div>
                                    <div>{{ $member->user->email}}</div>
                                    <div>Membership ID: <strong>{{ $member->membership->mid ? $member->membership->mid : 'NA'}}</strong></div>
                                </div>
                            </div>
                            <div class="item">
                                <div class="title">Existing Member</div>
                                <div class="image">
                                    @if($duplicate->user->avatar)
                                        <img src="{{ url('storage/images/'. $duplicate->user->avatar) }}" alt="" class="list-profile-photo" />
                                    @endif
                                </div>
                                <div class="details">
                                    <div class="name">{{ $duplicate->user->name}}</div>
                                    <div>{{ $duplicate->user->email}}</div>
                                    <div>Membership ID: <strong>{{ $duplicate->membership->mid}}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">MERGE</button>
                </div>
            </form>
        </div>
    </div>
</div>