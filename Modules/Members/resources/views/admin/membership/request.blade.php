@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Pending Requests</h1>
    </div>
</div>
<div class="page-content">
    <table class="table table-list">
        <tr>
            <th></th>
            <th>Name</th>
            <th>Request Status</th>
            <th>Requested On</th>
            <th></th>
        </tr>
        @foreach ($requests as $request)
        <tr>
            <td><img src="{{ url('storage/images/'. $request->user->avatar) }}" alt="{{ $request->member->name }}" title="{{ $request->member->name }}" class="list-profile-photo" /></td>
            <td>{{ $request->member->name }}</td>
            <td>{{ $request->request_status->name }}</td>
            <td>{{ date('d M, Y H:i A', strtotime($request->created_at)) }}</td>
            <td class="actions">
                <a href="/admin/members/member/view/{{ $request->user->id }}"><i class="fa-solid fa-eye"></i></a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection