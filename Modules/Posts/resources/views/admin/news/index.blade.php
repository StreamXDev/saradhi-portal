@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">News</h1>
    <div>
        @can('post.create')<a href="/admin/posts/create" class="btn btn-primary">Add News</a>@endcan
    </div>
</div>
<div class="page-content">
    <table class="table list">
        <thead>
            <tr>
                <th></th>
                <th>Title</th>
                <th>Location</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($posts as $post)    
            <tr>
                <td>
                    <span class="thumb">
                        @if($post->thumb)<img src="{{ url('storage/images/news/'. $post->thumb) }}" alt="" class="img-thumb">
                        @else <div class="no-thumb"></div> @endif
                    </span>
                </td>
                <td>{{$post->title}}</td>
                <td>{{$post->location}}</td>
                <td>{{$post->date}}</td>
                <td>{{$post->active ? 'Published' : 'Unpublished' }}</td>
                <td>
                    <div class="actions">
                        <a href="/admin/posts/{{ $post->id }}" class="btn"><i class="fa-solid fa-eye"></i></a>
                        @can('post.edit')<a href="/admin/posts/{{$post->id}}/edit" class="btn btn-outline-primary"><i class="fa-solid fa-pencil"></i></a>@endcan
                        @can('post.delete')
                        <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" onSubmit="if(!confirm('Are you sure want to delete this news?')){return false;}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">{{ $posts->appends(request()->query())->links() }}</div>
</div>
@endsection