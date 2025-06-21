@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Trustees</h1>
        <div><small class="text-muted">Showing <strong>{{$trustees->currentPage()}}</strong> to <strong>{{$trustees->count()}}</strong> of <strong>{{$trustees->total()}}</strong> results</small></div>
    </div>
    <div class="actions"></div>
</div>
<div class="page-search">
    <div class="page-title box-title">
        <h2 class="title">Search &amp; Filter</h2>
    </div>
    <form action="" method="">
        <div class="form-group no-margin">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search_by" id="search_by" placeholder="Name/Email/Phone/TID/MID" class="form-control" value="{{ $filters['search_by'] }}">
                </div>
                <div class="col-md-2">
                    <select name="status" id="search_status" class="form-select">
                        <option value="">Status</option>
                        <option value="active" @if($filters['status'] == 'active') selected @endif>Active</option>
                        <option value="inactive" @if($filters['status'] == 'inactive') selected @endif>Inactive</option>
                        <option value="terminated" @if($filters['status'] == 'terminated') selected @endif>Terminated</option>
                    </select>
                </div>
                <div class="col-md-7" style="display: flex; align-items:center; column-gap:0.5rem">
                    <input type="submit" name="search" value="Search" class="btn btn-primary">
                    <input type="button" value="CLEAR" class="btn btn-outline-default" onClick="clearForm();">
                    <button type="submit" name="export" value="export" class="btn btn-outline-default btn-right" style="margin-left: auto"><i class="fa-regular fa-file-excel"></i> Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="page-content">
    <div class="list-container">
        <table class="table list">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Trustee ID</th>
                    <th>MID</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trustees as $key => $trustee)    
                    <tr>
                        <td>
                            <div class="list-profile-photo">
                            @if($trustee->user->avatar)
                                <img src="{{ url('storage/images/'. $trustee->user->avatar) }}" alt="{{ $trustee->user->name }}" title="{{ $trustee->user->name }}" />
                            @else
                                <img src="{{ $trustee->member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="" style="width: 100%; opacity:0.2">
                            @endif
                            </div>
                        </td>
                        <td>{{ ucwords(strtolower($trustee->user->name)) }}</td>
                        <td>{{ $trustee->tid }}</td>
                        <td>{{ $trustee->member->membership->mid }}</td>
                        <td>{{ ucwords(strtolower($trustee->status)) }}</td>
                        <td>
                            <div class="actions">
                                <a href="/admin/members/member/view/{{ $trustee->user->id }}" class="btn"><i class="fa-solid fa-eye"></i></a>
                                <a href="/admin/trustees/delete/{{$trustee->id}}" class="btn" onclick="return confirm('Are you sure want to delete?');"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div>{{ $trustees->links() }}</div>
    </div>
</div>


@endsection
@section('page_scripts')