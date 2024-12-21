@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">ADs</h1>
    <div>
        <a href="/admin/ads/{{$ad->id}}/edit" class="btn btn-outline-primary">Edit</a>
    </div>
</div>
<div class="page-content">
    <div class="post-view">
        <div class="header">
            <div class="image">
                <img src="{{ url('storage/images/ads/'. $ad->image) }}" alt="" class="img-thumb">
            </div>
            <div class="details">
                <div class="link"><a href="{{$ad->link}}" target="_blank">{{$ad->link}}</a></div>
                <div class="link">Order: {{$ad->order}}</div>
            </div>
        </div>
    </div>
</div>
@endsection