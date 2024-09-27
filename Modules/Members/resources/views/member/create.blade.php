@extends('members::layouts.master')

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="page-title">
            <h1 class="title">Membership Registration</h1>
        </div>

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

        <div class="auth-form">
            <form action="{{ route('member.register') }}" method="POST" id="registerForm">
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
                <div class="form-group">
                    <label for="password" class="control-label">Password</label>
                    <div class="col">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password">
                        <div class="form-text">Your password must be at least 8 characters including atleaset a special character and a number</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="control-label">Confirm Password</label>
                    <div class="col">
                        <input type="password" name="password_confirmation" id="confirm_password" class="form-control" placeholder="Enter password again">
                    </div>
                </div>
                <input type="hidden" name="type" value="member">
                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script src="https://www.google.com/recaptcha/api.js?render={{ env('GOOGLE_RECAPTCHA_KEY') }}"></script>
<script type="text/javascript">
    $('#registerForm').submit(function(event) {
        event.preventDefault();
    
        grecaptcha.ready(function() {
            grecaptcha.execute("{{ env('GOOGLE_RECAPTCHA_KEY') }}", {action: 'subscribe_newsletter'}).then(function(token) {
                $('#registerForm').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                $('#registerForm').unbind('submit').submit();
            });;
        });
    });
</script>
@endsection

