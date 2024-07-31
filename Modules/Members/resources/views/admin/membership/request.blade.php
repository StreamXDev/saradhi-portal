@extends('layouts.admin')

@section('content')
<div class="container">
    
    <table class="table">
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
            <td>
                <a href="/admin/members/member/view/{{ $request->user->id }}" class="btn btn-primary">View</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection