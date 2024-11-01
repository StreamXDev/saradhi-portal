@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Pending Requests</h1>
    </div>
</div>
<div class="page-content">
    <div class="pf-tab">
        <ul class="nav nav-underline" id="requestListTab">
            <li class="nav-item">
                <a href="#submit" class="nav-link active"  id="submits" data-bs-toggle="tab" data-bs-target="#submitsPane">Submitted</a>
            </li>
            <li class="nav-item">
                <a href="#review" class="nav-link"  id="reviews" data-bs-toggle="tab" data-bs-target="#reviewsPane">Reviewed</a>
            </li>
            <li class="nav-item">
                <a href="#approve" class="nav-link"  id="approves" data-bs-toggle="tab" data-bs-target="#approvesPane">Approved</a>
            </li>
        </ul>
        <div class="tab-content" id="requestListTabContent">
            <div class="tab-pane fade show active" id="submitsPane">
                <table class="table table-list">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Request Status</th>
                        <th>Requested On</th>
                        <th></th>
                    </tr>
                    @foreach ($requests as $request)
                    @if($request->request_status->name == 'Submitted')
                    <tr>
                        <td><img src="{{ url('storage/images/'. $request->user->avatar) }}" alt="{{ $request->member->name }}" title="{{ $request->member->name }}" class="list-profile-photo" /></td>
                        <td>{{ $request->member->name }}</td>
                        <td>{{ $request->request_status->name }}</td>
                        <td>{{ date('d M, Y H:i A', strtotime($request->created_at)) }}</td>
                        <td class="actions">
                            <a href="/admin/members/member/view/{{ $request->user->id }}"><i class="fa-solid fa-eye"></i></a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </table>
            </div>
            <div class="tab-pane" id="reviewsPane">
                <table class="table table-list">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Request Status</th>
                        <th>Requested On</th>
                        <th></th>
                    </tr>
                    @foreach ($requests as $request)
                    @if($request->request_status->name == 'Reviewed')
                    <tr>
                        <td><img src="{{ url('storage/images/'. $request->user->avatar) }}" alt="{{ $request->member->name }}" title="{{ $request->member->name }}" class="list-profile-photo" /></td>
                        <td>{{ $request->member->name }}</td>
                        <td>{{ $request->request_status->name }}</td>
                        <td>{{ date('d M, Y H:i A', strtotime($request->created_at)) }}</td>
                        <td class="actions">
                            <a href="/admin/members/member/view/{{ $request->user->id }}"><i class="fa-solid fa-eye"></i></a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </table>
            </div>
            <div class="tab-pane" id="approvesPane">
                <table class="table table-list">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Request Status</th>
                        <th>Requested On</th>
                        <th></th>
                    </tr>
                    @foreach ($requests as $request)
                    @if($request->request_status->name == 'Approved')
                    <tr>
                        <td><img src="{{ url('storage/images/'. $request->user->avatar) }}" alt="{{ $request->member->name }}" title="{{ $request->member->name }}" class="list-profile-photo" /></td>
                        <td>{{ $request->member->name }}</td>
                        <td>{{ $request->request_status->name }}</td>
                        <td>{{ date('d M, Y H:i A', strtotime($request->created_at)) }}</td>
                        <td class="actions">
                            <a href="/admin/members/member/view/{{ $request->user->id }}"><i class="fa-solid fa-eye"></i></a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    
</div>
@endsection