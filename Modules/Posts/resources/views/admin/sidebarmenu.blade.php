
<li>
    <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#post_menu" aria-expanded="true"><i class="icon" data-feather="tv"></i>News</a>
    <div class="collapse sub-nav @if(isset($menuParent) && $menuParent == 'news') show @endif" id="post_menu">
        <ul class="nav btn-toggle-nav">
            <li><a href="/admin/posts" class="nav-item">List</a></li>
            @can('post.create')<li><a href="/admin/posts/create" class="nav-item">Add New</a></li>@endcan
        </ul>
    </div>
</li>
<li>
    <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#ads_menu" aria-expanded="true"><i class="icon" data-feather="image"></i>Ads</a>
    <div class="collapse sub-nav @if(isset($menuParent) && $menuParent == 'ads') show @endif" id="ads_menu">
        <ul class="nav btn-toggle-nav">
            <li><a href="/admin/ads" class="nav-item">List</a></li>
            @can('post.create')<li><a href="/admin/ads/create" class="nav-item">Add New</a></li>@endcan
        </ul>
    </div>
</li>
<li>
    <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#articles_menu" aria-expanded="true"><i class="icon" data-feather="book-open"></i>Krithikal</a>
    <div class="collapse sub-nav @if(isset($menuParent) && $menuParent == 'articles') show @endif" id="articles_menu">
        <ul class="nav btn-toggle-nav">
            <li><a href="/admin/articles" class="nav-item">List</a></li>
            @can('post.create')<li><a href="/admin/articles/create" class="nav-item">Add New</a></li>@endcan
        </ul>
    </div>
</li>
