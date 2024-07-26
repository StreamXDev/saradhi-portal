<div class="sidebar container">
    <nav class="aside">
        <ul class="nav">
            <li class="nav-item"><a href="admin/" class="nav-link">Dashboard</a></li>
        </ul>
        @if(Module::has('Members'))
            @include('members::admin.sidebarmenu')
        @endif
    </nav>
</div>