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
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>New Data</th>
                                <th>Existing Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="subtitle">
                                <td colspan="4">Personal Details</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="avatar" id="avatar_select"></td>
                                <td class="label photo"><label for="avatar_select">Photo</label></td>
                                <td>
                                    @if($member->user->avatar)
                                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="" class="list-profile-photo" />
                                    @endif
                                </td>
                                <td>
                                    @if($duplicate->user->avatar)
                                        <img src="{{ url('storage/images/'. $duplicate->user->avatar) }}" alt="" class="list-profile-photo" />
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="name" id="name_select"></td>
                                <td class="label"><label for="name_select">Name</label></td>
                                <td>{{ $member->user->name}}</td>
                                <td>{{ $duplicate->user->name}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="email" id="email_select"></td>
                                <td class="label"><label for="email_select">Email</label></td>
                                <td>{{ $member->user->email}}</td>
                                <td>{{ $duplicate->user->email}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="phone" id="phone_select"></td>
                                <td class="label"><label for="phone_select">Phone</label></td>
                                <td>@if($member->user->phone)+{{$member->user->calling_code}} {{$member->user->phone}} @endif</td>
                                <td>@if($duplicate->user->phone)+{{$duplicate->user->calling_code}} {{$duplicate->user->phone}} @endif</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="whatsapp" id="whatsapp_select"></td>
                                <td class="label"><label for="whatsapp_select">Whatsapp</label></td>
                                <td>@if($member->details->whatsapp)+{{$member->details->whatsapp_code}} {{$member->details->whatsapp}}@endif</td>
                                <td>@if($duplicate->details->whatsapp)+{{$duplicate->details->whatsapp_code}} {{$duplicate->details->whatsapp}}@endif</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="emergency_phone" id="emergency_phone_select"></td>
                                <td class="label"><label for="emergency_phone_select">Emergency Phone</label></td>
                                <td>@if($member->details->emergency_phone)+{{$member->details->emergency_phone_code}} {{$member->details->emergency_phone}}@endif</td>
                                <td>@if($duplicate->details->emergency_phone)+{{$duplicate->details->emergency_phone_code}} {{$duplicate->details->emergency_phone}}@endif</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="blood_group" id="blood_group_select"></td>
                                <td class="label"><label for="blood_group_select">Blood Group</label></td>
                                <td>{{$member->blood_group}}</td>
                                <td>{{$duplicate->blood_group}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="dob" id="dob_select"></td>
                                <td class="label"><label for="dob_select">DOB</label></td>
                                <td>{{date('d M, Y',strtotime($member->details->dob))}}</td>
                                <td>{{date('d M, Y',strtotime($duplicate->details->dob))}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="passport_no" id="passport_no_select"></td>
                                <td class="label"><label for="passport_no_select">Passport No.</label></td>
                                <td>{{$member->details->passport_no}}</td>
                                <td>{{$duplicate->details->passport_no}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="passport_expiry" id="passport_expiry_select"></td>
                                <td class="label"><label for="passport_expiry_select">Passport Expiry</label></td>
                                <td>{{date('d M, Y',strtotime($member->details->passport_expiry))}}</td>
                                <td>{{date('d M, Y',strtotime($duplicate->details->passport_expiry))}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="profession" id="profession_select"></td>
                                <td class="label"><label for="profession_select">Profession</label></td>
                                <td>{{$member->details->profession}}</td>
                                <td>{{$duplicate->details->profession}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="company" id="company_select"></td>
                                <td class="label"><label for="company_select">Company</label></td>
                                <td>{{$member->details->company}}</td>
                                <td>{{$duplicate->details->company}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="company_address" id="company_address_select"></td>
                                <td class="label"><label for="company_address_select">Company Address</label></td>
                                <td>{{$member->details->company_address}}</td>
                                <td>{{$duplicate->details->company_address}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="paci" id="paci_select"></td>
                                <td class="label"><label for="paci_select">PACI Number</label></td>
                                <td>{{$member->details->paci}}</td>
                                <td>{{$duplicate->details->paci}}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="governorate" id="governorate_select"></td>
                                <td class="label"><label for="governorate_select">Governorate</label></td>
                                <td>@isset($member->localAddress->governorate) {{ $member->localAddress->governorate }} @endisset</td>
                                <td>@isset($duplicate->localAddress->governorate) {{ $duplicate->localAddress->governorate }} @endisset</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="unit" id="unit_select"></td>
                                <td class="label"><label for="unit_select">Unit</label></td>
                                <td>@isset($member->details->member_unit->name) {{ $member->details->member_unit->name }} @endisset</td>
                                <td>@isset($duplicate->details->member_unit->name) {{ $duplicate->details->member_unit->name }} @endisset</td>
                            </tr>
                            <tr class="subtitle">
                                <td colspan="4">Kuwait Address</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="local_address_line_1" id="local_address_line_1_select"></td>
                                <td class="label"><label for="local_address_line_1_select">Area</label></td>
                                <td>@isset($member->localAddress->line_1) {{ $member->localAddress->line_1 }} @endisset</td>
                                <td>@isset($duplicate->localAddress->line_1) {{ $duplicate->localAddress->line_1 }} @endisset</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="local_address_building" id="local_address_building_select"></td>
                                <td class="label"><label for="local_address_building_select">Building</label></td>
                                <td>@isset($member->localAddress->building) {{ $member->localAddress->building }} @endisset</td>
                                <td>@isset($duplicate->localAddress->building) {{ $duplicate->localAddress->building }} @endisset</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="local_address_flat" id="local_address_flat_select"></td>
                                <td class="label"><label for="local_address_flat_select">Flat</label></td>
                                <td>@isset($member->localAddress->flat) {{ $member->localAddress->flat }} @endisset</td>
                                <td>@isset($duplicate->localAddress->flat) {{ $duplicate->localAddress->flat }} @endisset</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="local_address_floor" id="local_address_floor_select"></td>
                                <td class="label"><label for="local_address_floor_select">Floor</label></td>
                                <td>@isset($member->localAddress->floor) {{ $member->localAddress->floor }} @endisset</td>
                                <td>@isset($duplicate->localAddress->floor) {{ $duplicate->localAddress->floor }} @endisset</td>
                            </tr>
                            <tr class="subtitle">
                                <td colspan="4">India Address</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="permanent_address" id="permanent_address_select"></td>
                                <td class="label"><label for="permanent_address_select">Address</label></td>
                                <td>
                                    @if($member->permanentAddress !== null)
                                        {{ $member->permanentAddress->line_1 }}<br />
                                        @if ($member->permanentAddress->line_2 != null ) {{ $member->permanentAddress->line_2 }} <br /> @endif
                                        @if ($member->permanentAddress->city != null ) {{ $member->permanentAddress->city }} <br /> @endif
                                        @if ($member->permanentAddress->district != null ) {{ $member->permanentAddress->district }} <br /> @endif
                                        @if ($member->permanentAddress->region != null ) {{ $member->permanentAddress->region }} <br /> @endif
                                        @if ($member->permanentAddress->country != null ) {{ $member->permanentAddress->country }} <br /> @endif
                                        @if ($member->permanentAddress->zip != null ) {{ $member->permanentAddress->zip }} <br /> @endif
                                    @endif    
                                </td>
                                <td>
                                    @if($duplicate->permanentAddress !== null)
                                        {{ $duplicate->permanentAddress->line_1 }}<br />
                                        @if ($duplicate->permanentAddress->line_2 != null ) {{ $duplicate->permanentAddress->line_2 }} <br /> @endif
                                        @if ($duplicate->permanentAddress->city != null ) {{ $duplicate->permanentAddress->city }} <br /> @endif
                                        @if ($duplicate->permanentAddress->district != null ) {{ $duplicate->permanentAddress->district }} <br /> @endif
                                        @if ($duplicate->permanentAddress->region != null ) {{ $duplicate->permanentAddress->region }} <br /> @endif
                                        @if ($duplicate->permanentAddress->country != null ) {{ $duplicate->permanentAddress->country }} <br /> @endif
                                        @if ($duplicate->permanentAddress->zip != null ) {{ $duplicate->permanentAddress->zip }} <br /> @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="permanent_address_contact" id="permanent_address_contact_select"></td>
                                <td class="label"><label for="permanent_address_contact_select">Contact</label></td>
                                <td>@isset($member->permanentAddress->contact) +{{ $member->permanentAddress->contact }} @endisset</td>
                                <td>@isset($duplicate->permanentAddress->contact) +{{ $duplicate->permanentAddress->contact }} @endisset</td>
                            </tr>
                            <tr class="subtitle">
                                <td colspan="4">SNDP Details</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="sndp_branch" id="sndp_branch_select"></td>
                                <td class="label"><label for="sndp_branch_select">Branch</label></td>
                                <td>{{ $member->details->sndp_branch }}</td>
                                <td>{{ $duplicate->details->sndp_branch }}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="sndp_branch_number" id="sndp_branch_number_select"></td>
                                <td class="label"><label for="sndp_branch_number_select">Number</label></td>
                                <td>{{ $member->details->sndp_branch_number }}</td>
                                <td>{{ $duplicate->details->sndp_branch_number }}</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" checked name="sndp_union" id="sndp_union_select"></td>
                                <td class="label"><label for="sndp_union_select">Union</label></td>
                                <td>{{ $member->details->sndp_union }}</td>
                                <td>{{ $duplicate->details->sndp_union }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">MERGE</button>
                </div>
            </form>
        </div>
    </div>
</div>