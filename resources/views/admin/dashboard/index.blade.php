@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Dashboard</h1>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="page-content">
            <div>
                <h6><strong>Upload Event Result</strong></h6>
                <form action="{{ route('admin.result') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="result" id="result">
                    <input type="submit" value="Submit" class="btn btn-primary">
                </form>
            </div>
            <div style="margin-top: 30px">
                You can view the result at <a href="https://saradheeyam.com/result.html" target="_blank">https://saradheeyam.com/result.html</a>
            </div>
        </div>
    </div>
</div>
@endsection
