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
            @if(Module::has('Events'))
                @include('events::admin.sidebarmenu')
            @endif
            @if(Module::has('Posts'))
                @include('posts::admin.sidebarmenu')
            @endif
            @if(Module::has('PushNotification'))
                @include('pushnotification::backend.sidebarmenu')
            @endif
            <li>
                <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#settings_menu" aria-expanded="true"><i class="icon" data-feather="users"></i>Settings</a>
                <div class="collapse sub-nav" id="settings_menu">
                    <ul class="nav btn-toggle-nav">
                        @canany(['role.create', 'role.view','role.edit','role.delete','role.approve'])
                            <li><a href="/admin/roles" class="nav-item">Roles</a></li>
                        @endcan
                        @canany(['user.create', 'user.view','user.edit','user.delete','user.approve'])
                            <li><a href="/admin/users" class="nav-item">Users</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>