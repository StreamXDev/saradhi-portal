@extends('layouts.admin')


@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">View Committee</h1>
    </div>
    <div class="actions">
        
    </div>
</div>
<div class="page-content">
    <div>
        <div><strong>{{$committee->committee_type->name}}</strong></div>
        @if($committee->unit)<div><span class="label">Unit: </span><span>{{$committee->unit->name}}</span></div>@endif
        <div><span class="label">Formed On: </span><span>{{$committee->formed_on}}</span></div>
    </div>
    <hr />
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>Designation</th>
                    <th>Member Name</th>
                    <th>MID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($committee->committee_members as $item)
                <tr>
                    <td>{{$item->designation->name}}</td>
                    <td>{{$item->member->user->name}}</td>
                    <td>{{$item->member->membership->mid}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection