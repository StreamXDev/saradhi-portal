@extends('layouts.admin')

@section('content')
@isset($prevPage)
    {{$prevPage}}
@endisset
<div class="profile-view pf-default">
    <div class="pf-main">
        <div class="pf-face">
            <div class="photo">
                <div class="photo-profile">
                    @if($member->user->avatar)
                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="list-profile-photo" />
                    @else
                        <img src="{{ $member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="">
                    @endif
                </div>
                <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editBasics">
                    <i class="icon" data-feather="edit-2"></i>
                </button>
            </div>
            <div class="col-left">
                <div class="info">
                    <div class="pf-info-item pf-name">{{ $member->name }} <span class="status-pill {{ $member->membership->status }}">{{ $member->membership->status }}</span></div>
                    <div class="pf-info-item">{{ $member->user->email }}</div>
                    <div class="pf-info-item"><i class="icon" data-feather="phone"></i>+{{ $member->user->calling_code }}{{ $member->user->phone }}</div>
                    <div class="pf-info-item"><span class="label">Membership ID: </span> <span class="value"><strong>{{ $member->membership->mid ? $member->membership->mid : 'NA' }}</strong></span></div>
                    <div class="pf-info-item"><span class="label">Civil ID: </span> <span class="value"><strong>{{ $member->details->civil_id }}</strong></span></div>
                    @foreach ($member->relations as $relation)
                        @if($relation->relatedMember)
                        <div class="member-relation-box">
                            <div class="box-content">
                                <div class="image">
                                    @if($relation->relatedMember->user->avatar)
                                        <img src="{{ url('storage/images/'. $relation->relatedMember->user->avatar) }}" alt="{{ $relation->relatedMember->name }}" title="{{ $relation->relatedMember->name }}" class="list-profile-photo" />
                                    @else
                                        <img src="{{ $member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="">
                                    @endif
                                </div>
                                <div class="value">
                                    {{ ucfirst($relation->relationship->slug) }} of: <strong>{{ $relation->relatedMember->name }}</strong>
                                </div>
                                <a href="/admin/members/member/view/{{ $relation->relatedMember->user->id}}" class="btn btn-default">VIEW</a>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                @include('members::admin.includes.profile.actions')
            </div>
        </div>
        @if($duplicates)
        <div class="pf-duplicate-container">
            <div class="header">
                <div class="title">Duplicates Found</div>
                <div class="title-info">We found another member with the same <strong>Civil ID</strong>. If these are same person, you can merge the new data with old.</div>
            </div>
            @foreach($duplicates as $duplicate)
            <div class="pf-duplicates">
                <div class="photo photo-profile">
                    @if($duplicate->user->avatar)
                        <img src="{{ url('storage/images/'. $duplicate->user->avatar) }}" alt="{{ $duplicate->user->name }}" title="{{ $duplicate->user->name }}" class="list-profile-photo" />
                    @else
                        <img src="{{ $duplicate->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="">
                    @endif
                </div>
                <div class="col-left">
                    <div class="pf-name">{{ $duplicate->name }} 
                        <div class="status-pill {{ $duplicate->membership->status }}">{{ $duplicate->membership->status }}</div>
                    </div>
                    <div class="info">
                        <div class="col">
                            <div class="item">{{ $duplicate->user->email }}</div>
                            @if($duplicate->user->phone)<div class="item">+{{ $duplicate->user->calling_code }}{{ $duplicate->user->phone }}</div>@endif
                        </div>
                        
                        <div class="col">
                            <div class="item"><span class="label">Membership ID: </span> <span class="value"><strong>{{ $duplicate->membership->mid ? $duplicate->membership->mid : 'NA' }}</strong></span></div>
                            <div class="item"><span class="label">Civil ID: </span> <span class="value"><strong>{{ $duplicate->details->civil_id }}</strong></span></div>
                        </div>
                        <div class="col">
                            <div class="item">Member since {{date('d M, Y',strtotime($duplicate->membership->start_date))}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-action">
                    <a href="#" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#mergeMembers">Merge</a>
                </div>
                @include('members::admin.includes.profile.merge')
            </div>
            @endforeach
        </div>
        @endif
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
                        <a class="nav-link" id="relation_tab" data-bs-toggle="tab" data-bs-target="#relation_tab_pane" role="tab" aria-controls="relation_tab_pane" aria-selected="false">Family</a>
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
                                                    <td class="value">@isset($member->localAddress->governorate) {{ $member->localAddress->governorate }} @endisset</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Area</td>
                                                    <td class="value">@isset($member->localAddress->line_1) {{ $member->localAddress->line_1 }} @endisset</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Building</td>
                                                    <td class="value">@isset($member->localAddress->building) {{ $member->localAddress->building }} @endisset</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Flat</td>
                                                    <td class="value">@isset($member->localAddress->flat) {{ $member->localAddress->flat }} @endisset</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Floor</td>
                                                    <td class="value">@isset($member->localAddress->floor) {{ $member->localAddress->floor }} @endisset</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Unit</td>
                                                    <td class="value">@isset($member->details->member_unit->name) {{ $member->details->member_unit->name }} @endisset</td>
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
                                        @if($member->permanentAddress !== null)
                                            {{ $member->permanentAddress->line_1 }}<br />
                                            @if ($member->permanentAddress->line_2 != null ) {{ $member->permanentAddress->line_2 }} <br /> @endif
                                            @if ($member->permanentAddress->city != null ) {{ $member->permanentAddress->city }} <br /> @endif
                                            @if ($member->permanentAddress->district != null ) {{ $member->permanentAddress->district }} <br /> @endif
                                            @if ($member->permanentAddress->region != null ) {{ $member->permanentAddress->region }} <br /> @endif
                                            @if ($member->permanentAddress->country != null ) {{ $member->permanentAddress->country }} <br /> @endif
                                            @if ($member->permanentAddress->zip != null ) {{ $member->permanentAddress->zip }} <br /> @endif
                                        @endif
                                        <table class="list-basic">
                                            <tbody>
                                                <tr>
                                                    <td class="label">Contact</td>
                                                    <td class="value">@isset($member->permanentAddress->contact) +{{ $member->permanentAddress->contact }} @endisset</td>
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
                                                    <td class="value">@if($member->details->whatsapp !== null)+{{ $member->details->whatsapp_code }} {{ $member->details->whatsapp }}@endif</td>
                                                </tr>
                                                <tr>
                                                    <td class="label">Emergency No.</td>
                                                    <td class="value">@if($member->details->emergency_phone_code)+{{ $member->details->emergency_phone_code }} {{ $member->details->emergency_phone }}@endif</td>
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
                        <div class="page-col-wrapper">
                            <div class="col-main">
                                <div class="card notes">
                                    <div class="card-header">
                                        <div class="title">Notes and Remarks</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="notes-form">
                                            <form action="{{ route('admin.member.notes.add') }}" method="post">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{$member->user_id}}">
                                                <div class="form-group">
                                                    <textarea name="note" id="note"  rows="3" class="form-control" placeholder="Write notes"></textarea>
                                                </div> 
                                                <div class="form-group">
                                                    <button type="submit" name="submit" class="btn btn-xs btn-outline-info">Save Note</button>
                                                </div>
                                            </form>
                                        </div>
                                        <ul class="notes-list">
                                            @foreach ($member->notes as $item)    
                                                <li>
                                                    <div class="text">{{ $item->notes }}</div>    
                                                    <div class="footer">
                                                        <div class="info">By <span class="name">{{$item->createdBy->name}}</span> on <span class="date">{{$item->created_at}}</span></div>
                                                        <div class="actions">
                                                            <a href="/admin/members/member/notes/delete/{{$item->id}}" onclick="return confirm('Are you sure want to delete?');"><i class="icon" data-feather="trash"></i></a>
                                                        </div>
                                                    </div>
                                                </li>      
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    </div>
                    <div class="tab-pane fade" id="membership_tab_pane" role="tabpanel" aria-labelledby="membership_tab" tabindex="0">Membership</div>
                    <div class="tab-pane fade" id="relation_tab_pane" role="tabpanel" aria-labelledby="relation_tab" tabindex="0">
                        <div class="tab-content-header">
                            <div class="title">@if($member->relations)Family Members @else No family members found. @endif</div>
                            @if($member->type == 'primary')
                            <div class="actions">
                                <a href="/admin/members/member/family/create/{{$member->user_id}}" class="btn btn-primary">Add Family Member</a>
                            </div>
                            @endif
                        </div>
                        @if ($member->relations)
                            <div class="relative-cards">
                                @foreach ($member->relations as $relative)
                                    @if($relative->relatedMember)

                                        <div class="relative-card">
                                            <div class="card-body">
                                                <div class="photo photo-profile">
                                                    @if($relative->relatedMember->user->avatar)
                                                        <img src="{{ url('storage/images/'. $relative->relatedMember->user->avatar) }}" alt="{{ $relative->relatedMember->user->name }}" />
                                                    @else
                                                        <img src="{{ $relative->relatedMember->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="">
                                                    @endif
                                                </div>
                                                <div class="card-content">
                                                    <div class="title">{{$relative->relatedMember->user->name}}</div>
                                                    <div class="info">{{ ucfirst($relative->relationship->name)}}</div>
                                                    <div class="info">MID: {{ $relative->relatedMember->membership->mid }}</div>
                                                    <div class="info">Civil ID: {{ $relative->relatedMember->details->civil_id }}</div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="actions">
                                                    <a href="/admin/members/member/view/{{ $relative->relatedMember->user->id}}" class="btn btn-xs icon" ><i class="icon" data-feather="eye"></i></a>
                                                    <!-- <a href="/admin/members/member/edit/{{ $relative->relatedMember->user->id}}" class="btn btn-xs icon" ><i class="icon" data-feather="edit-2"></i></a> -->
                                                </div>
                                            </div>
                                        </div>
                                        
                                    @elseif ($relative->relatedDependent)

                                        <div class="relative-card">
                                            <div class="card-body">
                                                <div class="photo photo-profile">
                                                    @if($relative->relatedDependent->avatar)
                                                        <img src="{{ $relative->relatedDependent->avatar }}" alt="{{ $relative->relatedDependent->name }}" title="{{ $relative->relatedDependent->name }}"  />
                                                    @else
                                                        <img src="{{ $relative->relatedDependent->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="">
                                                    @endif
                                                </div>
                                                <div class="card-content">
                                                    <div class="title">{{$relative->relatedDependent->name}}</div>
                                                    <div class="info">{{ ucfirst($relative->relatedDependent->type)}} - {{ ucfirst($relative->relatedDependent->gender)}}</div>
                                                    <div class="info">DOB: {{ date('M d, Y', strtotime($relative->relatedDependent->dob)) }}</div>
                                                    <div class="info">Email: {{ $relative->relatedDependent->email }}</div>
                                                    <div class="info">Passport No: {{ $relative->relatedDependent->passport_no }}</div>
                                                    <div class="info">Passport Expiry: {{ $relative->relatedDependent->passport_expiry }}</div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="actions">
                                                    <a href="/admin/members/member/family/edit/{{$relative->relatedDependent->id}}" class="btn btn-xs icon" ><i class="icon" data-feather="edit-2"></i></a>
                                                    <form action="{{ route('admin.member.family.delete') }}" method="POST" onSubmit="if(!confirm('Are you sure want to delete?')){return false;}">
                                                        @csrf
                                                        <input type="hidden" name="dependent_id" value="{{$relative->relatedDependent->id}}">
                                                        <input type="hidden" name="primary_member" value="{{$member->user_id}}">
                                                        <button type="submit" name="delete" class="btn btn-xs icon"><i class="icon" data-feather="trash"></i></button>
                                                </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="pf-aside">
        @include('members::admin.includes.profile.actions')
        @if($statuses !== null && $request_action !== null)
            <div class="card card-warning">
                <div class="card-header">
                    <div class="title">Membership Status</div>
                </div>
                <div class="card-body">
                    <ul class="request-status-list">
                        @foreach ($statuses as $status)
                            <li class="{{ $status->checked ? 'active' : '' }}{{$status->slug == 'rejected' ? '-rejected' : ''}} {{ strtolower($status->name)}}" >
                                <div class="title">{{ $status->name }}</div>
                                @if($status->checked)<div class="date">On {{ date('d-m-Y h:i a', strtotime($status->updated_at)) }}</div>@endif
                                <div class="description">{{ $status->description }}</div>
                                @if($status->remark)<div class="remark"><strong>Remark: </strong>{{ $status->remark }}</div>@endif
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
                <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editMembership">
                    <i class="icon" data-feather="edit-2"></i>
                </button>
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
                        <div class="value">{{ $member->membership->family_in == 'kuwait' ? 'Family' : 'Single' }}</div>
                    </li>
                    <li>
                        <span class="label">Membership Status</span>
                        <div class="value {{ $member->membership->status =='active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($member->membership->status) }}</div>
                    </li>
                    <li>
                        <span class="label">Joining Date</span>
                        <div class="value">{{ date('M d, Y', strtotime($member->membership->start_date)) }}</div>
                    </li>
                    <li>
                        <span class="label">Expiry Date</span>
                        <div class="value">{{ date('M d, Y', strtotime($member->membership->expiry_date)) }}</div>
                    </li>
                </ul>
            </div>
        </div>
        @if($member->is_trustee)
        <div class="card">
            <div class="card-header">
                <div class="title">Trustee Details</div>
                <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editTrustee">
                    <i class="icon" data-feather="edit-2"></i>
                </button>
                @include('members::admin.member.edit.trustee')
            </div>
            <div class="card-body">
                <ul class="list-basic">
                    <li>
                        <span class="label">Trustee ID</span>
                        <div class="value"><strong>{{$member->trustee->tid}}</strong></div>
                    </li>
                    <li>
                        <span class="label">Title</span>
                        <div class="value">{{$member->trustee->title}}</div>
                    </li>
                    <li>
                        <span class="label">Joining Date</span>
                        <div class="value">{{$member->trustee->joining_date}}</div>
                    </li>
                    <li>
                        <span class="label">Status</span>
                        <div class="value">{{ucfirst($member->trustee->status)}}</div>
                    </li>
                </ul>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header">
                <div class="title">Trustee Details</div>
            </div>
            <div class="card-body">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTrustee">Add to Trustees</a>
                @include('members::admin.member.create_trustee')
            </div>
        </div>
        @endif
        @if(!$committees->isEmpty())
            <div class="card">
                <div class="card-header">
                    <div class="title">Committee Details</div>
                </div>
                <div class="card-body">
                    <ul>
                        @foreach ($committees as $item)
                            <li>
                                <div><strong>{{$item->designation->name}}</strong></div>
                                @if($item->committee->unit){{$item->committee->unit->name}}@endif {{$item->committee->committee_type->name}}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-header">
                <div class="title">Introducer Info</div>
                <button type="button" class="btn btn-xs icon" data-bs-toggle="modal" data-bs-target="#editIntroducer">
                    <i class="icon" data-feather="edit-2"></i>
                </button>
                @include('members::admin.member.edit.introducer')
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
                            <td class="value">@if($member->membership->introducer_phone) +{{ $member->membership->introducer_phone_code }}{{ $member->membership->introducer_phone }}@endif</td>
                        </tr>
                        <tr>
                            <td class="label">Introudcer MID</td>
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
        <div style="color: #999">
            <small>U: {{$member->user->id}}</small> | 
            <small>M: {{$member->id}}</small> | 
            <small>MS: {{$member->membership->mid}}</small>
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


<!-- Member Request Delete -->
<div class="modal fade" id="memberDeleteModal" tabindex="-1" aria-labelledby="memberDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.member.delete') }}" method="post" id="deleteMemberForm">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="memberDeleteModalLabel">Delete Member</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group text-danger"><strong>Do you want to delete the request?<br /> This action can't be undone.</strong></div>
                    <div class="mt-2">Please type <strong>Delete {{$member->name}}</strong></strong></div>
                    <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                    <input type="hidden" name="user_name" id="del_user_name" value="{{ $member->name }}">
                    <div class="form-group">
                        <input type="text" name="confirm_text" id="delete_confirm_text" class="form-control">
                        <div class="invalid-feedback">
                            Invalid text.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="delete_member" class="btn btn-success">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End: Member Request delete -->
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
        $('#delete_member').on('click', function(e){
            e.preventDefault();
            $('#delete_confirm_text').removeClass('is-invalid');
            var memberName = $('#del_user_name').val();
            var confirmText = $('#delete_confirm_text').val();
            if(confirmText !== 'Delete '+memberName || confirmText === '' || confirmText === undefined){
                $('#delete_confirm_text').addClass('is-invalid');
            }else{
                $('#deleteMemberForm').submit();
            }
        });
    });
</script>
@endsection