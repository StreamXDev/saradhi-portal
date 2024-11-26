<div class="modal fade" id="mergeMembers" tabindex="-1" aria-labelledby="mergeMembersLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="post">
                @csrf
                
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="mergeMembersLabel">Merge Members</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="merge-title"><strong>Alert: The following data will be copied from this membership request to existing member's details</strong></div>
                    <div class="data-compare">
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
                                    <td></td>
                                    <td class="label photo">Photo</td>
                                    <td>
                                        @if($duplicate->user->avatar)
                                            <img src="{{ url('storage/images/'. $duplicate->user->avatar) }}" alt="" class="list-profile-photo" />
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->user->avatar)
                                            <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="" class="list-profile-photo" />
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Email</td>
                                    <td>{{ $duplicate->user->email}}</td>
                                    <td>{{ $member->user->email}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Phone</td>
                                    <td>@if($duplicate->user->phone)+{{$duplicate->user->calling_code}} {{$duplicate->user->phone}} @endif</td>
                                    <td>@if($member->user->phone)+{{$member->user->calling_code}} {{$member->user->phone}} @endif</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Whatsapp</td>
                                    <td>@if($duplicate->details->whatsapp)+{{$duplicate->details->whatsapp_code}} {{$duplicate->details->whatsapp}}@endif</td>
                                    <td>@if($member->details->whatsapp)+{{$member->details->whatsapp_code}} {{$member->details->whatsapp}}@endif</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Emergency Phone</td>
                                    <td>@if($duplicate->details->emergency_phone)+{{$duplicate->details->emergency_phone_code}} {{$duplicate->details->emergency_phone}}@endif</td>
                                    <td>@if($member->details->emergency_phone)+{{$member->details->emergency_phone_code}} {{$member->details->emergency_phone}}@endif</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Blood Group</td>
                                    <td>{{$duplicate->blood_group}}</td>
                                    <td>{{$member->blood_group}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">DOB</td>
                                    <td>{{date('d M, Y',strtotime($duplicate->details->dob))}}</td>
                                    <td>{{date('d M, Y',strtotime($member->details->dob))}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Passport No.</td>
                                    <td>{{$member->details->passport_no}}</td>
                                    <td>{{$member->details->passport_no}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Passport Expiry</td>
                                    <td>{{date('d M, Y',strtotime($duplicate->details->passport_expiry))}}</td>
                                    <td>{{date('d M, Y',strtotime($member->details->passport_expiry))}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Profession</td>
                                    <td>{{$duplicate->details->profession}}</td>
                                    <td>{{$member->details->profession}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Company</td>
                                    <td>{{$duplicate->details->company}}</td>
                                    <td>{{$member->details->company}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Company Address</td>
                                    <td>{{$duplicate->details->company_address}}</td>
                                    <td>{{$member->details->company_address}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">PACI Number</td>
                                    <td>{{$duplicate->details->paci}}</td>
                                    <td>{{$member->details->paci}}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Governorate</td>
                                    <td>@isset($duplicate->localAddress->governorate) {{ $duplicate->localAddress->governorate }} @endisset</td>
                                    <td>@isset($member->localAddress->governorate) {{ $member->localAddress->governorate }} @endisset</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Unit</td>
                                    <td>@isset($duplicate->details->member_unit->name) {{ $duplicate->details->member_unit->name }} @endisset</td>
                                    <td>@isset($member->details->member_unit->name) {{ $member->details->member_unit->name }} @endisset</td>
                                </tr>
                                <tr class="subtitle">
                                    <td colspan="4">Kuwait Address</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Area</td>
                                    <td>@isset($duplicate->localAddress->line_1) {{ $duplicate->localAddress->line_1 }} @endisset</td>
                                    <td>@isset($member->localAddress->line_1) {{ $member->localAddress->line_1 }} @endisset</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Building</td>
                                    <td>@isset($duplicate->localAddress->building) {{ $duplicate->localAddress->building }} @endisset</td>
                                    <td>@isset($member->localAddress->building) {{ $member->localAddress->building }} @endisset</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Flat</td>
                                    <td>@isset($duplicate->localAddress->flat) {{ $duplicate->localAddress->flat }} @endisset</td>
                                    <td>@isset($member->localAddress->flat) {{ $member->localAddress->flat }} @endisset</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Floor</td>
                                    <td>@isset($duplicate->localAddress->floor) {{ $duplicate->localAddress->floor }} @endisset</td>
                                    <td>@isset($member->localAddress->floor) {{ $member->localAddress->floor }} @endisset</td>
                                </tr>
                                <tr class="subtitle">
                                    <td colspan="4">India Address</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Address</td>
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
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Contact</td>
                                    <td>@isset($duplicate->permanentAddress->contact) +{{ $duplicate->permanentAddress->contact }} @endisset</td>
                                    <td>@isset($member->permanentAddress->contact) +{{ $member->permanentAddress->contact }} @endisset</td>
                                </tr>
                                <tr class="subtitle">
                                    <td colspan="4">SNDP Details</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Branch</td>
                                    <td>{{ $duplicate->details->sndp_branch }}</td>
                                    <td>{{ $member->details->sndp_branch }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Number</td>
                                    <td>{{ $duplicate->details->sndp_branch_number }}</td>
                                    <td>{{ $member->details->sndp_branch_number }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td class="label">Union</td>
                                    <td>{{ $duplicate->details->sndp_union }}</td>
                                    <td>{{ $member->details->sndp_union }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" disabled>MERGE</button>
                </div>
            </form>
        </div>
    </div>
</div>