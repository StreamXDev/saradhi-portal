@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">News</h1>
    <div>
        <a href="/admin/posts/{{$post->id}}/edit" class="btn btn-outline-primary">Edit</a>
    </div>
</div>
<div class="page-content">
    <div class="post-view">
        <div class="header">
            <div class="image">
                <img src="{{ url('storage/images/news/'. $post->thumb) }}" alt="" class="img-thumb">
            </div>
            <div class="details">
                <div class="title">{{$post->title}}</div>
                <div class="info">{{$post->location}}</div>
                <div class="info">{{$post->date}}</div>
            </div>
        </div>
        <div class="content">
            {!! $post->body !!}
        </div>
    </div>
</div>
@endsection