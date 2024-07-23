@extends('members::layouts.master')

@section('content')
<div class="container">
    <div class="page-title">
        <div class="title">Successfully Registrered.</div>
        <div class="sub-title">Your membershipt request is waiting for approval. You will get notified once it is approved. Thank you.</div>
    </div>
    <div class="pending-message">
        <div class="title">Current Status</div>
        @foreach ($pendings as $pending)
            <div class="item">
                <div class="name">{{ $pending->request_status->name }}</div>
                <div class="description">Your {{ $pending->request_status->description }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection