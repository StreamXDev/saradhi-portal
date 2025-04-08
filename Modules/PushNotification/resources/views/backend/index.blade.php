@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Push Notification</h1>
    </div>
    <div class="actions">
    </div>
</div>
<div class="page-content">
    <div class="notification-form">
        <div class="section-title">
            <h5 class="title">Send Notification</h5>
        </div>
        <form action="{{route('admin.pushnotification.send')}}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="title" class="form-label">Title</label>
                <div class="col-sm-12">
                    <input type="text" name="title" id="title" area-describedby="titleHelp" class="form-control counter-input" maxlength="65">
                    <div id="titleHelp" class="form-text">Maximum 65 Characters allowed. Remaining <span class="count">65</span> of 65 Characters</div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="description" class="form-label">Description</label>
                <div class="col-sm-12">
                    <textarea name="description" id="description" area-describedby="descriptionHelp" class="form-control counter-input" maxlength="110"></textarea>
                    <div id="descriptionHelp" class="form-text">Maximum 110 Characters allowed. Remaining <span class="count">110</span> of 110 Characters</div>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i data-feather="send"></i> Send</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('page_scripts')
<script type="module">
    var countEl = $('.counter-input');
    $.each(countEl, function(){
        $(this).keyup(function(){
            var count = this.value.length;
            var maxLength = $(this).attr('maxlength');
            $(this).closest('div').children().find('.count').text(parseInt(maxLength)-count);
        })
    })
</script>
@endsection