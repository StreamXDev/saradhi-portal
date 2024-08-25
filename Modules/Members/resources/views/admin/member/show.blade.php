@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="page-title">
        <a href="{{ url()->previous() }}" class="back btn">< Back</a>
        <div>
            @can('membership_request.export')
                <a href="/admin/members/member/pdf/{{ $member->user->id }}" class="btn btn-outline-primary">Export to PDF</a>
                <a href="/admin/members/member/excel/{{ $member->user->id }}" class="btn btn-outline-primary">Export to Excel</a>
            @endcan
        </div>
    </div>
    <div class="member-view">
        <div class="header">
            <div class="row">
                <div class="col-md-12">
                    @foreach ($member->relations as $relation)
                        @if ($relation->relatedTo->type == 'primary')
                            <div class="member-relation">
                                <div class="image"><img src="{{ url('storage/images/'. $relation->relatedTo->user->avatar) }}" alt="{{ $relation->relatedTo->name }}" title="{{ $relation->relatedTo->name }}" class="list-profile-photo" /></div>
                                <div class="value">
                                    {{ $member->name }} is {{$relation->relationship->slug }} of <br /><strong>{{ $relation->relatedTo->name }}</strong>
                                </div>
                                <a href="/admin/members/member/view/{{ $relation->relatedTo->user->id}}" class="btn btn-default">VIEW</a>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="col-md-2">
                    <div class="member-photo">
                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="list-profile-photo" />
                    </div>
                </div>
                <div class="col-md-10">
                    <ul class="detail-list">
                        <li>
                            <span class="label">Name</span>
                            <div class="value">{{ $member->name }}</div>
                        </li>
                        <li>
                            <span class="label">Email</span>
                            <div class="value">{{ $member->user->email }}</div>
                        </li>
                        <li>
                            <span class="label">Phone</span>
                            <div class="value">{{ $member->user->phone }}</div>
                        </li>
                        <li>
                            <span class="label">Civil ID</span>
                            <div class="value">{{ $member->details->civil_id }}</div>
                        </li>
                        <li>
                            <span class="label">Member Type</span>
                            <div class="value">{{ ucfirst($member->type) }}</div>
                        </li>
                        <li>
                            <span class="label">Unit</span>
                            <div class="value">{{ $member->details->member_unit->name }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="data-section">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="list-title">Mebership Details</h5>
                    <ul class="detail-list">
                        <li>
                            <span class="label">Membership ID</span>
                            <div class="value"><strong>{{ $member->membership->mid }}</strong></div>
                        </li>
                        <li>
                            <span class="label">Membership Type</span>
                            <div class="value">{{ ucfirst($member->membership->type) }}</div>
                        </li>
                        
                        <li>
                            <span class="label">Membership Status</span>
                            <div class="value {{ $member->membership->status =='active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($member->membership->status) }}</div>
                        </li>
                    </ul>
                    
                    <h5 class="list-title">Basic Details</h5>
                    <ul class="detail-list">
                        <li>
                            <span class="label">Gender</span>
                            <div class="value">{{ ucfirst($member->gender) }}</div>
                        </li>
                        <li>
                            <span class="label">Date of birth</span>
                            <div class="value">{{ date('M d, Y', strtotime($member->details->dob)) }}</div>
                        </li>
                        <li>
                            <span class="label">Passport No.</span>
                            <div class="value">{{ $member->details->passport_no }}</div>
                        </li>
                        <li>
                            <span class="label">Passport Expiry</span>
                            <div class="value">{{ date('M d, Y', strtotime($member->details->passport_expiry)) }}</div>
                        </li>
                        <li>
                            <span class="label">Company</span>
                            <div class="value">{{ $member->details->company }}</div>
                        </li>
                        <li>
                            <span class="label">Profession</span>
                            <div class="value">{{ $member->details->profession }}</div>
                        </li>
                        
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="proof_list">
                        <li>
                            <div class="image">
                                <img src="{{ url('storage/images/'. $member->details->photo_civil_id_front) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="image-fluid"  style="width:160px" />
                            </div>
                            <div class="title">Civil ID 01</div>
                        </li>
                        <li>
                            <div class="image">
                                <img src="{{ url('storage/images/'. $member->details->photo_civil_id_back) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"  class="image-fluid"  style="width:160px"/>
                            </div>
                            <div class="title">Civil ID 02</div>
                        </li>
                        <li>
                            <div class="image">
                                <img src="{{ url('storage/images/'. $member->details->photo_passport_front) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"  class="image-fluid"  style="width:160px"/>
                            </div>
                            <div class="title">Passport copy - 01</div>
                        </li>
                        <li>
                            <div class="image">
                                <img src="{{ url('storage/images/'. $member->details->photo_passport_back) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"  class="image-fluid"  style="width:160px"/>
                            </div>
                            <div class="title">Passport copy - 02</div>
                        </li>
                    </ul>
                </div>
            </div>
            <div>
                <ul class="request-status-list">
                    @foreach ($statuses as $status)
                        <li class={{ $status->checked ? 'active' : '' }}{{$status->slug == 'rejected' ? '-rejected' : ''}}>
                            <div class="title">{{ $status->name }}</div>
                            <div class="description">{{ $status->description }}</div>
                            <div class="remark">{{ $status->remark }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif
            @if($request_action)
                @if($current_status->request_status->slug == 'approved' && $request_action->action['slug'] == 'confirm')
                    @if(Auth::user()->can('membership_request.confirm'))
                    <div class="request-confirmation">
                        <form action="{{ route('admin.member.confirm_membership_request') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                            <div class="form-group">
                                <label for="mid" class="control-label">Member Id</label>
                                <div>Suggested Member Id: <strong>{{ $suggested_mid }}</strong></div>
                                <div class="col"><input type="text" name="mid" id="mid" class="form-control"></div>
                            </div>
                            <div class="form-group">
                                <textarea name="remark" id="remark" rows="5" class="form-control" placeholder="Enter comments"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </form>
                    </div>
                    @endif
                @else
                    <div class="request-action">
                        <form action="{{ route('admin.member.change_status') }}" method="post">
                            @csrf
                            @if($current_status->rejected != null)
                                <div class="form-group">The request is already rejected. Do you want to {{ $request_action->action['slug'] }} the action?</div>
                            @else
                                <div class="form-group">Are you sure want to {{ $request_action->action['slug'] }} the request?</div>
                            @endif
                            <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                            <input type="hidden" name="current_status_id" value="{{ $request_action->request_status_id }}">
                            <div class="form-group">
                                <textarea name="remark" id="remark" cols="30" rows="5" placeholder="Enter comment (Optional)" class="form-control"></textarea>
                            </div>
                            @if($current_status->rejected == null)
                                <button type="submit" class="btn btn-success" name="action" value="submit">{{ $request_action->action['name'] }}</button>
                            @else
                                <button type="submit" class="btn btn-warning" name="action" value="submit">{{ $request_action->action['name'] }}</button>
                            @endif
                        </form>
                    </div>
                @endif
            @endif
            
        </div>
    </div>
</div>
@endsection