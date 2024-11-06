@extends('layouts.admin')
@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
@endsection
@section('content')
<div class="page-title">
    <h1 class="title">Add Krithikal</h1>
</div>
<div class="page-content">
    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
        @csrf
    
        <div class="form-group">
            <label class="form-label" for="inputName">Title</label>
            <input type="text" name="title" id="inputName" class="form-control @error('title') is-invalid @enderror" placeholder="News Title">
            @error('title')
                <div><small class="text-danger">{{ $message }}</small></div>
            @enderror
        </div>
     
        <div class="form-group">
            <label class="form-label" for="inputEmail">Body</label>
            <div id="quill-editor" class="mb-3" style="height: 300px;"></div>
            <textarea rows="3" class="mb-3 d-none" name="body" id="quill-editor-area"></textarea>
            @error('body')
                <div><small class="text-danger">{{ $message }}</small></div>
            @endif
        </div>
        
        <div class="form-group row">
            <div class="col-md-3">
                <label class="form-label" for="thumb">Featured Image</label>
                <div class="control-col">
                    <input type="file" name="thumb" id="thumb" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <label for="order" class="form-label">Order</label>
                <div class="control-col">
                    <input type="text" name="order" id="order" class="form-control">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-2">
                <input type="checkbox" name="active" id="active" checked><label for="active">Published</label>
            </div>
        </div>
        <div class="form-group">
            <button class="btn btn-success btn-submit">Submit</button>
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