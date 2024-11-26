<div class="actions profile-actions">
    <ul class="action-buttons">
        @can('membership_request.export')
        @if($member->active)
            <li>
                <a href="#" class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#idCardModal"><i class="icon" data-feather="credit-card"></i> ID Card</a>
            </li>
        @endif
        <li>
            <a href="#" class="btn btn-xs btn-outline-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon" data-feather="file"></i> Export</a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="/admin/members/member/pdf/{{ $member->user->id }}" class="dropdown-item">Export to PDF</a></li>
                <li><a href="/admin/members/member/excel/{{ $member->user->id }}" class="dropdown-item">Export to Excel</a></li>
            </ul>
        </li>
        @endcan
        <li>
            <a href="#" class="btn btn-xs btn-outline-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon" data-feather="more-vertical"></i></a>
        </li>
    </ul>
</div>