@extends('layouts.admin')

@section('content')
<div class="page-title">
    <div class="title-container">
        <h1 class="title">Edit Committee</h1>
    </div>
</div>
<div class="page-content">
    <form action="{{ route('admin.committee.update') }}" method="POST">
        @csrf
        <input type="hidden" name="committee_id" value="{{$committee->id}}">
        <div class="committee-details">
            <div>
                <h4 class="title"><strong>{{$committee->committee_type->name}}</strong></h4>
                <div class="form-group row">
                    @if($committee->unit)
                    <div class="col-md-2">
                        <label for="unit" class="form-label">Unit</label>
                        <div class="form-col">
                            <select name="member_unit_id" id="unit" class="form-select">
                                @foreach ($units as $unit)
                                    <option value="{{$unit->id}}" @selected($unit->id == $committee->member_unit_id ? true : false)>{{$unit->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <div class="form-col">
                            <select name="status" id="status" class="form-select">
                                <option value="1" @selected($committee->active == 1 ? true : false)>Active</option>
                                <option value="0" @selected($committee->active == 0 ? true: false)>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="formed_on" class="form-label">Formed On</label>
                        <div class="form-col">
                            <input type="date" name="formed_on" id="formed_on" value="{{$committee->formed_on}}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top: 2rem">
            <h4>Members</h4>
            <div class="add-designation">
                <div class="title">Add Committee Members</div>
                <div class="form-group row" id="item">
                    <div class="col-md-4 col-lg-2">
                        <label for="designation_title" class="form-label">Designation</label>
                        <select name="title" id="designation_title" class="form-select">
                            <option value="">Select</option>
                            @foreach ($designations as $designation)
                                <option value="{{$designation->id}}" data-title="{{$designation->name}}">{{$designation->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label for="" class="form-label">Select Member</label>
                        <input class="typeahead form-control" id="search" type="text" autocomplete="off" placeholder="Search Name">
                    </div>
                </div>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Designation</th>
                    <th>Member</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="typeHeadResult">
                @foreach ($members as $member)    
                <tr id="thrRow{{$member->user->id}}">
                    <td>
                        <input type="hidden" name="designation[]" value="{{$member->designation_id}}">
                        {{$member->designation->name}}
                    </td>
                    <td>
                        <input type="hidden" name="members[]" value="{{$member->user->id}}">
                        <div class="profile-pill">
                            <div class="avatar">
                                @if($member->user->avatar)
                                    <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}" />
                                @else
                                    <img src="{{ $member->member->gender == 'male' ? url('images/avatar-male.jpeg') : url('images/avatar-female.png') }}" alt="" style="width: 100%; opacity:0.2">
                                @endif
                            </div>
                            <div class="details">
                                <div class="title">{{$member->user->name}}</div>
                                <div>{{$member->user->email}}</div>
                                <div>MID: {{$member->member->membership->mid}}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-remove-typeHead" data-id="{{$member->user->id}}" onclick="return confirm('Are you sure want to remove?');"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
@section('page_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script>
    $(document).ready(function(){
        

        var designation_title = $('#designation_title').find(':selected').data('title');
        var designation_id = $('#designation_title').find(':selected').val();
        toggleSearchInput(designation_id);
        $('#designation_title').on('change', function(){
            designation_title = $(this).find(':selected').data('title');
            designation_id = $(this).find(':selected').val();
            $('#search').prop('disabled', false);
            toggleSearchInput(designation_id);
        });

        var path = "{{ route('admin.committee.autocomplete') }}";   
        $('#search').typeahead( {
            name: 'best-pictures',
            displayKey: 'value',
            source: function (query, process) {
                return $.get(path, {
                    query: query
                }, function (data) {
                    return process(data);
                })
            },
            updater: function (item) {
                $('#typeHeadResult').append(
                    '<tr id="thrRow'+item.user.id+'">'
                    +'<td><input type="hidden" name="designation[]" class="form-control" value="'+designation_id+'">'+designation_title+'</td>'
                    +'<td><input type="hidden" name="members[]" value="'+item.user.id+'">'
                        +'<div class="profile-pill">'
                            +`<div class="avatar">${item.user.avatar !== null ? '<img src="/storage/images/'+item.user.avatar+'"></div>' : item.gender == 'male' ? '<img src="/images/avatar-male.jpeg">' : '<img src="/images/avatar-female.png">'}</div>`
                            +'<div class="details"><div class="title">'+item.user.name+'</div><div>'+item.user.email+'</div><div>MID: '+item.membership.mid+'</div></div>'
                        +'</div>'
                    +'</td>' 
                    +'<td><div class="actions"><a class="btn btn-remove-typeHead" data-id="'+item.user.id+'"><i class="fa-solid fa-trash"></i></a></div></td>'
                    +'</tr>'
                );       
            }
        });
        $(document).on('click', '.btn-remove-typeHead',function(){
            var id = $(this).data("id");
            $(this).closest('#thrRow'+id).remove();
        });
    });

    function toggleSearchInput(handle){
        if(handle == ''){
            $('#search').prop('disabled', true);
        }
    }

    
</script>
@endsection