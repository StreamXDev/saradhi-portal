@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">Create Ad</h1>
</div>
<div class="page-content">
    <form method="POST" action="{{ route('admin.ads.store') }}" enctype="multipart/form-data">
        @csrf
    
        <div class="form-group row">
            <div class="col-md-3">
                <label class="form-label" for="image">Image</label>
                <div class="control-col">
                    <input type="file" name="image" id="image" class="form-control">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="link" class="form-label">Link</label>
                <div class="control-col">
                    <input type="text" name="link" id="link" class="form-control">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-2">
                <label for="order" class="form-label">Position</label>
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