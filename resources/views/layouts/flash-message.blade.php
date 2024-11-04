@session('success')
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ $value }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endsession
      
@session('error')
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    @if($errors->any())
        <div class="form-errors">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @else
        {{ $value }}
    @endif
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endsession
       
@session('warning')
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    {{ $value }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endsession
       
@session('info')
<div class="alert alert-info alert-dismissible fade show" role="alert">
    {{ $value }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endsession
