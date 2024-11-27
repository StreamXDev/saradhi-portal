@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">Krithikal</h1>
    <div>
        <a href="/admin/articles/{{$article->id}}/edit" class="btn btn-outline-primary">Edit</a>
    </div>
</div>
<div class="page-content">
    <div class="post-view">
        <div class="header">
            <div class="image">
                <img src="{{ url('storage/images/articles/'. $article->thumb) }}" alt="" class="img-thumb">
            </div>
            <div class="details">
                <div class="title">{{$article->title}}</div>
                <div class="info">{{$article->date}}</div>
            </div>
        </div>
        <div class="content">
            {!! $article->body !!}
        </div>
    </div>
</div>
@endsection