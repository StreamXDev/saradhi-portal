@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">Krithikal</h1>
    <div>
        @can('post.create')<a href="/admin/articles/create" class="btn btn-primary">Add New</a>@endcan
    </div>
</div>
<div class="page-content">
    <table class="table list">
        <thead>
            <tr>
                <th></th>
                <th>Title</th>
                <th>order</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($articles as $article)    
            <tr>
                <td>
                    <span class="thumb">
                        @if($article->thumb)<img src="{{ url('storage/images/articles/'. $article->thumb) }}" alt="" class="img-thumb">
                        @else <div class="no-thumb"></div> @endif
                    </span>
                </td>
                <td>{{$article->title}}</td>
                <td>{{$article->order}}</td>
                <td>{{$article->active ? 'Published' : 'Unpublished' }}</td>
                <td>
                    <div class="actions">
                        <a href="/admin/articles/{{ $article->id }}" class="btn"><i class="fa-solid fa-eye"></i></a>
                        @can('post.edit')<a href="/admin/articles/{{$article->id}}/edit" class="btn"><i class="fa-solid fa-pencil"></i></a>@endcan
                        @can('post.delete')
                        <form method="POST" action="{{ route('admin.articles.destroy', $article->id) }}" onSubmit="if(!confirm('Are you sure want to delete this article?')){return false;}">
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
    <div class="pagination-container">{{ $articles->appends(request()->query())->links() }}</div>
</div>
@endsection