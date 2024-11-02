@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Trustees</h1>
        <div><small class="text-muted">Showing <strong>{{$trustees->currentPage()}}</strong> to <strong>{{$trustees->count()}}</strong> of <strong>{{$trustees->total()}}</strong> results</small></div>
    </div>
    <div class="actions">
        
    </div>
</div>
<div class="page-content">
    <div class="list-container">
        <table class="table list">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Trustee ID</th>
                    <th>Status</th>
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
                        <td>{{ ucwords(strtolower($trustee->status)) }}</td>
                        <td>
                            <div class="actions">
                                <a href="/admin/members/member/view/{{ $trustee->user->id }}" class="btn"><i class="fa-solid fa-eye"></i></a>
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