@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Pending Requests</h1>
        <div><small class="text-muted">Showing <strong>{{$requests->currentPage()}}</strong> to <strong>{{$requests->count()}}</strong> of <strong>{{$requests->total()}}</strong> results</small></div>
    </div>
</div>
<div class="page-content">
    <div class="pf-tab">
        <ul class="nav nav-underline" >
            <li class="nav-item">
                <a href="?type=submitted" class="nav-link @if($type == 'submitted') active @endif">Submitted</a>
            </li>
            <li class="nav-item">
                <a href="?type=verified" class="nav-link  @if($type == 'verified') active @endif">Verified</a>
            </li>
            <li class="nav-item">
                <a href="?type=reviewed" class="nav-link  @if($type == 'reviewed') active @endif">Reviewed</a>
            </li>
            <li class="nav-item">
                <a href="?type=approved" class="nav-link  @if($type == 'approved') active @endif">Approved</a>
            </li>
        </ul>
        <div class="tab-content" id="requestListTabContent">
            <div class="tab-pane fade show active" id="submitsPane">
                <table class="table list">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Civil ID</th>
                        <th>Request Status</th>
                        <th>Requested On</th>
                        <th></th>
                    </tr>
                    @foreach ($requests as $request)
                    <tr>
                        <td>
                            <div class="list-profile-photo">
                                <img src="{{ url('storage/images/'. $request->user->avatar) }}" alt="{{ $request->member->name }}" title="{{ $request->member->name }}"  />
                            </div>
                        </td>
                        <td>{{ $request->member->name }}</td>
                        <td>
                            {{ $request->details->civil_id }} 
                            @if($request->duplicate_civil_id)<div><small style="color:red">{{$request->duplicate_civil_id}} Duplicate Civil ID Found</small></div>@endif
                        </td>
                        <td>{{ $request->request_status->name }}</td>
                        <td>{{ date('d M, Y H:i A', strtotime($request->created_at)) }}</td>
                        <td>
                            <div class="actions">
                                <a href="/admin/members/member/view/{{ $request->user->id }}"><i class="fa-solid fa-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="pagination-container">{{ $requests->appends(request()->query())->links() }}</div>
            </div>
            
        </div>
    </div>
    
</div>
@endsection