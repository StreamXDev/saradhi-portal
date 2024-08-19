<div class="sidebar container">
    <nav class="aside">
        <a href="/admin" class="nav-link">Dashboard</a>
        @if(Module::has('Members'))
            @include('members::admin.sidebarmenu')
        @endif
        @canany(['role.create', 'role.view','role.edit','role.delete','role.approve'])
            <a href="/admin/roles" class="nav-link">Roles</a>
        @endcan
        @canany(['user.create', 'user.view','user.edit','user.delete','user.approve'])
            <a href="/admin/users" class="nav-link">Users</a>
        @endcan
    </nav>
</div>