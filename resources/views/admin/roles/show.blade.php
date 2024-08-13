@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2> Show Role</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {{ $role->name }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div>
                <strong>Permissions:</strong>
                <table class="table">
                    <tr>
                        <th>Group</th>
                        <th>Role</th>
                    </tr>
                    @if(!empty($rolePermissions))
                        @foreach($rolePermissions as $v)
                            <tr>
                                <td>{{ $v->group_name }}</td>
                                <td>{{ $v->name }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection