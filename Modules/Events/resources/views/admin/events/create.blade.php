<div>
    <!-- It is never too late to be what you might have been. - George Eliot -->
</div>
@extends('layouts.admin')
@section('page-style')
@endsection
@section('content')
<div class="page-title">
    <h1 class="title">Create Event</h1>
</div>
<div class="page-content">
    <div class="form-container">
        <form action="{{ route('admin.events.create') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title" class="form-label">Event Title <span class="asterisk">*</span></label>
                <div class="form-col">
                    <input type="text" name="title" id="title" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Event Description</label>
                <div class="form-col">
                    <textarea name="description" id="description" class="form-control"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date <span class="asterisk">*</span></label>
                    <div class="form-col">
                        <input type="text" name="start_date" id="start_date" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <div class="form-col">
                        <input type="text" name="end_date" id="end_date" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <div class="form-col">
                        <input type="text" name="start_time" id="start_time" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <div class="form-col">
                        <input type="text" name="end_time" id="end_time" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="location" class="form-label">Location</label>
                    <div class="form-col">
                        <input type="text" name="location" id="location" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="thumb" class="form-label">Thumbnail Image</label>
                    <div class="form-col">
                        <input type="file" name="thumb" id="thumb">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="cover" class="form-label">Cover Image</label>
                    <div class="form-col">
                        <input type="file" name="cover" id="cover">
                    </div>
                </div>
            </div>
            <h2 class="subtitle">Volunteers</h2>
            
        </form>
    </div>
</div>
@endsection