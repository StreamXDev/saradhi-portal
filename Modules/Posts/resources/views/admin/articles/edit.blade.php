@extends('layouts.admin')
@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
@endsection
@section('content')
<div class="page-title">
    <h1 class="title">Edit Krithikal</h1>
</div>
<div class="page-content">
    <form method="POST" action="{{ route('admin.articles.update', $article->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{$article->id}}">
        <div class="form-group">
            <label class="form-label" for="inputName">Title:</label>
            <input type="text" name="title" id="inputName" class="form-control @error('title') is-invalid @enderror" value="{{$article->title}}">
            @error('title')
                <div><small class="text-danger">{{ $message }}</small></div>
            @enderror
        </div>
     
        <div class="form-group">
            <label class="form-label" for="inputEmail">Body:</label>
            <div id="quill-editor" class="mb-3" style="height: 300px;">{!! $article->body !!}</div>
            <textarea rows="3" class="mb-3 d-none" name="body" id="quill-editor-area">{{$article->body}}</textarea>
            @error('body')
                <div><small class="text-danger">{{ $message }}</small></div>
            @endif
        </div>
        
        <div class="form-group row">
            <div class="col-md-4 row">
                <div class="col post-image">
                    <img src="{{ url('storage/images/articles/'. $article->thumb) }}" alt="" class="img-thumb">
                </div>
                <div class="col">
                    <label class="form-label" for="thumb">Change Image</label>
                    <div class="control-col">
                        <input type="file" name="thumb" id="thumb" class="form-control">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <div class="control-col">
                    <input type="date" name="date" id="date" class="form-control" value="{{$article->date}}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="order" class="form-label">Order</label>
                <div class="control-col">
                    <input type="text" name="order" id="order" class="form-control" value="{{$article->order}}">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-2">
                <input type="checkbox" name="active" id="active" @if($article->active) checked @endif><label for="active">Published</label>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success btn-submit">Submit</button>
        </div>
    </form>
</div>
@endsection
@section('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- Initialize Quill editor -->
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('quill-editor-area')) {
            var editor = new Quill('#quill-editor', {
                theme: 'snow'
            });
            var quillEditor = document.getElementById('quill-editor-area');
            editor.on('text-change', function() {
                quillEditor.value = editor.root.innerHTML;
            });

            quillEditor.addEventListener('input', function() {
                editor.root.innerHTML = quillEditor.value;
            });
        }
    });
</script>
@endsection