@extends('layouts.admin')

@section('content')
<div class="page-title">
    <h1 class="title">User Management</h1>
</div>
<div class="page-search">
    <div class="page-title box-title">
        <h2 class="title">Search &amp; Filter</h2>
    </div>
    <form action="" method="">
        <div class="form-group no-margin">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search_by" id="search_by" placeholder="Email/Phone/Name" class="form-control" value="{{ $filters['search_by'] }}">
                </div>
                <div class="col-md-2">
                    <input type="submit" value="Search" class="btn btn-primary">
                </div>
            </div>
        </div>
    </form>
</div>
<div class="page-content">
    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($users as $i => $user)
         <tr>
             <td>{{ ++$i }}</td>
             <td>{{ $user->name }}</td>
             <td>{{ $user->email }}</td>
             <td>
                @hasrole('superadmin')
               @if(!empty($user->getRoleNames()))
                 @foreach($user->getRoleNames() as $v)
                    <label class="badge bg-success">{{ $v }}</label>
                 @endforeach
               @endif
               @endhasrole
             </td>
             <td>
                 @can('user.view')
                  <a class="btn btn-info btn-sm" href="{{ route('users.show',$user->id) }}"><i class="fa-solid fa-list"></i> Show</a>
                 @endcan
                 @can('user.edit')
                  <a class="btn btn-primary btn-sm" href="{{ route('users.edit',$user->id) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                 @endcan
                 <!--
                 @can('user.delete')
                   <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display:inline">
                       @csrf
                       @method('DELETE')
     
                       <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                   </form>
                 @endcan
                 -->
             </td>
         </tr>
      @endforeach
     </table>
     
     
     {{ $users->appends(request()->query())->links() }}
</div>
@endsection