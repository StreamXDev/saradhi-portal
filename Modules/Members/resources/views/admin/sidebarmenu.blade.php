<ul class="nav">
    @if (Auth::user()->hasRole(['superadmin','admin','treasurer']))
        <li class="nav-item"><a href="/admin/members/requests" class="nav-link">Membership Requests</a></li>
    @endif
</ul>