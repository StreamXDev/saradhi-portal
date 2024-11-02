@canany([
    'event.view',
    'event.create',
    'event.edit',
    'event.delete',
])
    
    
    <li>
        <a href="#" class="nav-item btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#event_menu" aria-expanded="true"><i class="icon" data-feather="calendar"></i>Events</a>
        <div class="collapse sub-nav" id="event_menu">
            <ul class="nav btn-toggle-nav">
                <li><a href="/admin/events" class="nav-item">Upcoming Events</a></li>
                <li><a href="/admin/events?type=past" class="nav-item">Past Events</a></li>
            </ul>
        </div>
    </li>
@endcanany
