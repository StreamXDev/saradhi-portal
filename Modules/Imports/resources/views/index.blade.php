@extends('imports::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('imports.name') !!}</p>
@endsection
