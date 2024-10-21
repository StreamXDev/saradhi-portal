@extends('layouts.admin')
@section('page-style')
<style>
    .table tr td{
        vertical-align: middle
    }
</style>
@endsection
@section('content')
<div class="page-title">
    <h1 class="title">Events</h1>
    <div>
        <a href="/admin/events/create" class="btn btn-primary">Create New Event</a>
    </div>
</div>
<div class="page-content">
    <table class="table list">
        <thead>
            <tr>
                <th>Title</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection