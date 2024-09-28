@extends('layouts.admin')

@section('content')
<div class="profile-view pf-default">
    <div class="pf-main">
        <div class="pf-face">
            <div class="photo">
                @if($member->user->avatar)
                    <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="list-profile-photo" />
                @else
                    <img src="{{ $member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="">
                @endif
            </div>
            <div class="col-left">
                <div class="info">
                    <div class="pf-info-item pf-name">{{ $member->name }} <span class="status-pill {{ $member->membership->status }}">{{ $member->membership->status }}</span></div>
                    <div class="pf-info-item">{{ $member->user->email }}</div>
                    <div class="pf-info-item"><i class="icon" data-feather="phone"></i>+{{ $member->user->calling_code }}{{ $member->user->phone }}</div>
                    <div class="pf-info-item"><span class="label">Membership ID: </span> <span class="value"><strong>{{ $member->membership->mid ? $member->membership->mid : 'NA' }}</strong></span></div>
                    @foreach ($member->relations as $relation)
                        @if ($relation->relatedTo->type == 'primary')
                            <div class="member-relation-box">
                                <div class="box-content">
                                    <div class="image"><img src="{{ url('storage/images/'. $relation->relatedTo->user->avatar) }}" alt="{{ $relation->relatedTo->name }}" title="{{ $relation->relatedTo->name }}" class="list-profile-photo" /></div>
                                    <div class="value">
                                        {{ $member->name }} is {{$relation->relationship->slug }} of <strong>{{ $relation->relatedTo->name }}</strong>
                                    </div>
                                    <a href="/admin/members/member/view/{{ $relation->relatedTo->user->id}}" class="btn btn-default">VIEW</a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                @include('members::admin.includes.profile.actions')
            </div>
        </div>
        <div class="pf-content">
            <div class="pf-tab">
                <ul class="nav nav-underline" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="overview_tab" data-bs-toggle="tab" data-bs-target="#overview_tab_pane" role="tab" aria-controls="overview_tab_pane" aria-selected="true">Overview</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="membership_tab" data-bs-toggle="tab" data-bs-target="#membership_tab_pane" role="tab" aria-controls="membership_tab_pane" aria-selected="false">Membership</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="relation_tab" data-bs-toggle="tab" data-bs-target="#relation_tab_pane" role="tab" aria-controls="relation_tab_pane" aria-selected="false">Relations</a>
                    </li>
                </ul>
                <div class="tab-content" id="profileTabContent">
                    <div class="tab-pane fade show active" id="overview_tab_pane" role="tabpanel" aria-labelledby="overview_tab" tabindex="0">
                        <div class="page-col-wrapper">
                            <div class="col-sub">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="title">Local Address</div>
                                        <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editAddress">
                                            <i class="icon" data-feather="edit-2"></i>
                                        </button>
                                        @include('members::admin.member.edit.address')
                                    </div>
                                    <div class="card-body">
                                        <table class="list-basic">
                                            <tbody>
                                                <tr>
                                                    <td class="label">Governorate</td>
                                                    <td class="value">{{ $member->localAddress->governorate }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Area</td>
                                                    <td class="value">{{ $member->localAddress->line_1 }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Building</td>
                                                    <td class="value">{{ $member->localAddress->building }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Flat</td>
                                                    <td class="value">{{ $member->localAddress->flat }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Floor</td>
                                                    <td class="value">{{ $member->localAddress->floor }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Unit</td>
                                                    <td class="value">{{ $member->details->member_unit->name }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <div class="title">India Address</div>
                                        <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editAddress">
                                            <i class="icon" data-feather="edit-2"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        {{ $member->permanentAddress->line_1 }}<br />
                                        @if ($member->permanentAddress->line_2 != null ) {{ $member->permanentAddress->line_2 }} <br /> @endif
                                        @if ($member->permanentAddress->city != null ) {{ $member->permanentAddress->city }} <br /> @endif
                                        @if ($member->permanentAddress->district != null ) {{ $member->permanentAddress->district }} <br /> @endif
                                        @if ($member->permanentAddress->region != null ) {{ $member->permanentAddress->region }} <br /> @endif
                                        @if ($member->permanentAddress->country != null ) {{ $member->permanentAddress->country }} <br /> @endif
                                        @if ($member->permanentAddress->zip != null ) {{ $member->permanentAddress->zip }} <br /> @endif
                                        <table class="list-basic">
                                            <tbody>
                                                <tr>
                                                    <td class="label">Contact</td>
                                                    <td class="value">+{{ $member->permanentAddress->contact }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <div class="title">SNDP Info</div>
                                    </div>
                                    <div class="card-body">
                                        <table class="list-basic">
                                            <tbody>
                                                <tr>
                                                    <td class="label">Branch</td>
                                                    <td class="value">{{ $member->details->sndp_branch }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Branch Number</td>
                                                    <td class="value">{{ $member->details->sndp_branch_number }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Union</td>
                                                    <td class="value">{{ $member->details->sndp_union }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-main">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="title">Basic Info</div>
                                        <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editBasics">
                                            <i class="icon" data-feather="edit-2"></i>
                                        </button>
                                        @include('members::admin.member.edit.basic')
                                    </div>
                                    <div class="card-body">
                                        <table class="list-basic">
                                            <tbody>
                                                <tr>
                                                    <td class="label">Name</td>
                                                    <td class="value">{{ $member->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Email</td>
                                                    <td class="value">{{ $member->user->email }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Phone</td>
                                                    <td class="value">+{{ $member->user->calling_code }} {{ $member->user->phone }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Whatsapp</td>
                                                    <td class="value">+{{ $member->details->whatsapp_code }} {{ $member->details->whatsapp }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Emergency No.</td>
                                                    <td class="value">+{{ $member->details->emergency_phone_code }} {{ $member->details->emergency_phone }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Civil ID</td>
                                                    <td class="value">
                                                        {{ $member->details->civil_id }}
                                                        @if($member->details->photo_civil_id_front || $member->details->photo_civil_id_back)
                                                            <a href="#" class="link" data-bs-toggle="modal" data-bs-target="#civilIdProof"><i class="icon" data-feather="file-text"></i></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="label">PACI No.</td>
                                                    <td class="value">{{ $member->details->paci }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Unit</td>
                                                    <td class="value">{{ $member->details->member_unit->name }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <div class="title">Personal Info</div>
                                        <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editPersonal">
                                            <i class="icon" data-feather="edit-2"></i>
                                        </button>
                                        @include('members::admin.member.edit.personal')
                                    </div>
                                    <div class="card-body">
                                        <table class="list-basic">
                                            <tbody>
                                                <tr>
                                                    <td class="label">Gender</td>
                                                    <td class="value">{{ ucfirst($member->gender) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Blood Group</td>
                                                    <td class="value">{{ ucfirst($member->blood_group) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Date of birth</td>
                                                    <td class="value">{{ date('M d, Y', strtotime($member->details->dob)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Passport No.</td>
                                                    <td class="value">
                                                        {{ $member->details->passport_no }}
                                                        @if($member->details->photo_passport_front || $member->details->photo_passport_back)
                                                            <a href="#" class="link" data-bs-toggle="modal" data-bs-target="#passportProof"><i class="icon" data-feather="file-text"></i></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Passport Expiry</td>
                                                    <td class="value">
                                                        {{ date('M d, Y', strtotime($member->details->passport_expiry)) }}
                                                        @if($member->details->photo_passport_front || $member->details->photo_passport_back)
                                                            <a href="#" class="link" data-bs-toggle="modal" data-bs-target="#passportProof"><i class="icon" data-feather="file-text"></i></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Company Address</td>
                                                    <td class="value">{{ $member->details->company_address }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Company</td>
                                                    <td class="value">{{ $member->details->company }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Profession</td>
                                                    <td class="value">{{ $member->details->profession }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="membership_tab_pane" role="tabpanel" aria-labelledby="membership_tab" tabindex="0">Membership</div>
                    <div class="tab-pane fade" id="relation_tab_pane" role="tabpanel" aria-labelledby="relation_tab" tabindex="0">Relations</div>
                </div>
            </div>
        </div>
    </div>
    <div class="pf-aside">
        @include('members::admin.includes.profile.actions')
        @if($statuses != null)
            <div class="card card-warning">
                <div class="card-header">
                    <div class="title">Membership Status</div>
                </div>
                <div class="card-body">
                    <ul class="request-status-list">
                        @foreach ($statuses as $status)
                            <li class="{{ $status->checked ? 'active' : '' }}{{$status->slug == 'rejected' ? '-rejected' : ''}} {{ strtolower($status->name)}}" >
                                <div class="title">{{ $status->name }}</div>
                                <div class="description">{{ $status->description }}</div>
                                @if($status->remark)<div class="remark"><strong>Remark:</strong>{{ $status->remark }}</div>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if($request_action)
                    <div class="card-footer">
                        @include('members::admin.includes.membership.request.action')
                    </div>
                @endif
            </div>
        @endif
        <div class="card">
            <div class="card-header">
                <div class="title">Membership Details</div>
                <!--
                <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editMembership">
                    <i class="icon" data-feather="edit-2"></i>
                </button> 
                -->
                @include('members::admin.member.edit.membership')
            </div>
            <div class="card-body">
                <ul class="list-basic">
                    <li>
                        <span class="label">Membership ID</span>
                        <div class="value"><strong>{{ $member->membership->mid ? $member->membership->mid : 'NA' }}</strong></div>
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
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="title">Introducer Info</div>
            </div>
            <div class="card-body">
                <table class="list-basic">
                    <tbody>
                        <tr>
                            <td class="label">Name</td>
                            <td class="value">{{ $member->membership->introducer_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Phone</td>
                            <td class="value">+{{ $member->membership->introducer_phone_code }}{{ $member->membership->introducer_phone }}</td>
                        </tr>
                        <tr>
                            <td class="label">Membership ID</td>
                            <td class="value">{{ $member->membership->introducer_mid }}</td>
                        </tr>
                        <tr>
                            <td class="label">Unit</td>
                            <td class="value">{{ $member->membership->introducer_unit }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('members::admin.includes.membership.request.proof')
<!-- Modal -->
<div class="modal fade" id="idCardModal" tabindex="-1" aria-labelledby="idCardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="idCardModalLabel">ID Card</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <div class="id-card-wrapper">
                @include('members::includes.idcard')
            </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="screenshot">Download</button>
        </div>
      </div>
    </div>
</div>

@endsection
@section('page_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-zoom/1.6.1/jquery.zoom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    $('#screenshot').click(function(){
        var link = document.createElement('a');
        html2canvas(document.getElementById('idCard')).then(function(canvas) {
            var image = canvas.toDataURL();
            link.setAttribute('download', '<?php echo $member->name ?>'+'_Member-ID.png');
            link.href = image;
            link.click();
        });
    })

    $(document).ready(function(){
        //$('.zoom-photo').wrap('<span style="display:inline-block"></span>').css('display', 'block').parent().zoom();
    });
</script>
@endsection