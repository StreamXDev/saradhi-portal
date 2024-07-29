@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="page-title">
        <a href="{{ url()->previous() }}" class="back btn">< Back</a>
    </div>
    <div class="member-view">
        <div class="header">
            <div class="row">
                <div class="col-md-2">
                    <div class="member-photo">
                        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" class="list-profile-photo" />
                    </div>
                </div>
                <div class="col-md-10">
                    <ul class="detail-list">
                        <li>
                            <span class="label">Name</span>
                            <div class="value">{{ $member->name }}</div>
                        </li>
                        <li>
                            <span class="label">Email</span>
                            <div class="value">{{ $member->user->email }}</div>
                        </li>
                        <li>
                            <span class="label">Phone</span>
                            <div class="value">{{ $member->user->phone }}</div>
                        </li>
                        <li>
                            <span class="label">Member Type</span>
                            <div class="value">{{ ucfirst($member->type) }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div>
            <div class="row">
                <div class="col-md-8">
                    <h5 class="list-title">Mebership Details</h5>
                    <ul class="detail-list">
                        <li>
                            <span class="label">Membership Type</span>
                            <div class="value">{{ ucfirst($member->membership->type) }}</div>
                        </li>
                        <li>
                            <span class="label">Membership Status</span>
                            <div class="value {{ $member->membership->status =='active' ? 'text-success' : 'text-danger' }}">{{ ucfirst($member->membership->status) }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection