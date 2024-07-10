@extends('members::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('members.name') !!}</p>
@endsection
