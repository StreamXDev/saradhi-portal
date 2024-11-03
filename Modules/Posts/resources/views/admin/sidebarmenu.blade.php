
<li>
    <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#post_menu" aria-expanded="true"><i class="icon" data-feather="tv"></i>News</a>
    <div class="collapse sub-nav" id="post_menu">
        <ul class="nav btn-toggle-nav">
            <li><a href="/admin/posts" class="nav-item">List</a></li>
            @can('user.create')<li><a href="#" class="nav-item">Add New</a></li>@endcan
        </ul>
    </div>
</li>