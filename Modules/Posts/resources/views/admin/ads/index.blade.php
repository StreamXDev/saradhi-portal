@extends('layouts.admin')
@section('content')
<div class="page-title">
    <h1 class="title">Ads</h1>
    <div>
        <a href="/admin/ads/create" class="btn btn-primary">Create Ad</a>
    </div>
</div>
<div class="page-content">
    <table class="table list">
        <thead>
            <tr>
                <th>Image</th>
                <th>Link</th>
                <th>Order</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ads as $ad)    
            <tr>
                <td>
                    <span class="thumb">
                        @if($ad->image)<img src="{{ url('storage/images/ads/'. $ad->image) }}" alt="" class="img-thumb">
                        @else <div class="no-thumb"></div> @endif
                    </span>
                </td>
                <td>{{$ad->link}}</td>
                <td>{{$ad->order}}</td>
                <td>{{$ad->active ? 'Published' : 'Unpublished' }}</td>
                <td>
                    <div class="actions">
                        <a href="/admin/ads/{{ $ad->id }}" class="btn"><i class="fa-solid fa-eye"></i></a>
                        <a href="/admin/ads/{{$ad->id}}/edit" class="btn"><i class="fa-solid fa-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.ads.destroy', $ad->id) }}" onSubmit="if(!confirm('Are you sure want to delete this ad?')){return false;}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-container">{{ $ads->appends(request()->query())->links() }}</div>
</div>
@endsection