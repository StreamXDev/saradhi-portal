@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="col">
        <h1 class="title">Members</h1>
    </div>
</div>
<div class="page-content">
    <ul class="box-list">
        @forelse ($members as $member)  
        <li class="item">
            <div class="primary">
                <div class="avatar">
                    <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->name }}" title="{{ $member->name }}"  />
                </div>
                <div class="details">
                    <div class="col name">
                        {{ $member->name }}
                        <div class="sub"><span class="label">MID</span> {{ $member->membership->mid }}</div>
                    </div>
                    <div class="col unit"><span class="label">Unit</span>{{ $member->details->member_unit->name }}</div>
                    <div class="col type"><span class="label">Mem.Type</span>{{ ucfirst($member->membership->type) }} @if($member->membership->type == 'family' )<button class="relation-expand" data-target="mid{{ $member->membership->mid }}"><i class="fa-solid fa-circle-chevron-down"></i></button> @endif </div>
                    <div class="col status"><span class="label">Status</span> {{ ucfirst($member->membership->status) }} </div>
                </div>
                <div class="actions">
                    <a href="/admin/members/member/view/{{ $member->user->id }}"><i class="fa-solid fa-eye"></i></a>
                </div>
            </div>
            <div class="relations" id="mid{{ $member->membership->mid }}">
                @forelse ($member->relations as $relation)
                <div class="item">
                    <div class="relationship">{{ $relation->relationship->name }}</div>
                    <div class="details">
                        <div class="col name">{{ $relation->relatedTo->name }}</span></div>
                    </div>
                </div>
                @empty
                    No relations found.
                @endforelse 
            </div>
        </li>
        @empty
            No items found.
        @endforelse 
    </ul>
    {!! $members->withQueryString()->links('pagination::bootstrap-5') !!}
</div>
@endsection
@section('page_scripts')
<script>
    $(document).ready(function(){
        $('.relation-expand').click(function(){
            var id = $(this).data('target');
            if ($('#'+id).is(':hidden')){
                $('.box-list .item .relations').slideUp();
                $('#'+id).slideDown();
            }
        });
    });
</script>
@endsection