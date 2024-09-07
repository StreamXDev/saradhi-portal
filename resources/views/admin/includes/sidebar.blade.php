<nav class="sidebar" id="sidbar">
    <div class="title">
        <div class="logo">
            {{ config('app.name', 'Laravel') }}
        </div>
    </div>
    <div class="main-menu">
        <ul class="nav">
            <li><a href="/admin" class="nav-item"><i class="icon" data-feather="home"></i> Dashboard</a></li>
            @if(Module::has('Members'))
                @include('members::admin.sidebarmenu')
            @endif
            <!-- 
            @canany(['role.create', 'role.view','role.edit','role.delete','role.approve'])
                <a href="/admin/roles" class="nav-link">Roles</a>
            @endcan
            @canany(['user.create', 'user.view','user.edit','user.delete','user.approve'])
                <a href="/admin/users" class="nav-link">Users</a>
            @endcan
            -->
        </ul>
    </div>
</nav>