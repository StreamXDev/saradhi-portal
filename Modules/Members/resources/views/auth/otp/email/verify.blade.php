@extends('members::layouts.app')

@section('content')
<div class="container">
    <div class="page-title">
        <div class="title">Verify Email</div>
    </div>
    
    <p>
        Hi {{ request()->get('name') }}, You have registred successfully.
        Before proceeding,  please check your email for an email verification OTP.
    </p>

    <div style="margin-top: 1rem">
        @if($errors->any())
            <div class="form-errors">
                
                    {!! implode('', $errors->all('<div>:message</div>')) !!}
                
            </div>
        @endif
        <form action="{{ route('member.verify_email_otp') }}" method="POST">
            @csrf 
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="otp" id="otp" placeholder="Enter OTP" class="form-control">
                </div>
                <div class="col">
                    <input type="hidden" name="name" id="name" value="{{ request()->get('name') }}">
                    <input type="hidden" name="email" id="email" value="{{ request()->get('email') }}">
                    <button type="submit" name="submit" class="btn btn-primary">Verify</button>
                </div>
            </div>
        </form>
    </div>
    <br />
    <hr />
    <br />
    
    <form action="{{ route('member.resend_email_otp') }}" method="POST">
        @csrf 
        <input type="hidden" name="email" value="{{ request()->get('email') }}">
        <div>If you did not receive the email, <button type="submit" name="submit" style="display:inline-block; border:none; background:none; outline:none; color:blue; text-decoration:underline">Resend OTP</button></div>
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

    </form>
    
</div>
@endsection