@extends('members::layouts.master')

@section('content')
<div class="container">

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    <div class="page-title">
        <h1 class="title">Register</h1>
    </div>
    <form action="{{ route('member.register') }}" method="POST">
        @csrf 
        <div class="form-group">
            <label for="name" class="control-label">Name</label>
            <div class="col">
                <input type="name" name="name" id="name" placeholder="Your Name" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="email" class="control-label">Email</label>
            <div class="col">
                <input type="email" name="email" id="email" class="form-control" placeholder="Your Email">
            </div>
        </div>
        <input type="hidden" name="type" value="member">
        <div class="form-group">
            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
@endsection
