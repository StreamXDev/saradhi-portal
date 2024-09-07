@canany([
    'membership_request.verification.show',
    'membership_request.verification.verify',
    'membership_request.review.show',
    'membership_request.review.review',
    'membership_request.approval.show',
    'membership_request.approval.approve',
    'membership_request.confirm',
    'membership_request.export',
])
    
    <li><a href="/admin/members/requests" class="nav-item" ><i class="icon" data-feather="star"></i>Pending Requests</a></li>
@endcanany

<li>
    <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#member_menu" aria-expanded="true"><i class="icon" data-feather="users"></i>Members</a>
    <div class="collapse sub-nav" id="member_menu">
        <ul class="nav btn-toggle-nav">
            <li><a href="/admin/members" class="nav-item">List</a></li>
        </ul>
    </div>
</li>