@extends('layouts.admin')
@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Add Member</h1>
    </div>
</div>
<div class="page-content">
    @if($errors->any())
        <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif
    <form action="">
        @csrf
        <div class="form-section-title">Basic User Information</div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="name" class="form-label">Name<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Email<span class="asterisk">*</span></label>
                <div class="control-col">
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone<span class="asterisk">*</span></label>
                <div class="control-col">
                    <select name="tel_country_code" id="tel_contry_code" class="form-select country-code">
                        @foreach ($countries as $country)
                            <option value="{{ $country->calling_code }}" @if($country->code == 'kw') selected @endif>{{ $country->name }} (+{{ $country->calling_code}})</option>
                        @endforeach
                    </select>
                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                </div>
            </div>
        </div>
    </form>
</div>
@endsection