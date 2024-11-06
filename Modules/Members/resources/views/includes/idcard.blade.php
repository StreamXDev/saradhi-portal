<div class="id-card" id="idCard">
    <div class="photo">
        <img src="{{ url('storage/images/'. $member->user->avatar) }}" alt="{{ $member->user->name }}" title="{{ $member->user->name }}"/>
    </div>
    <div class="name">{{ $member->name }}</div>
    <div class="title-info">
        <span class="item">
            <i class="fa-solid fa-phone"></i> +{{$member->user->calling_code}}{{$member->user->phone}}
        </span>
        <span class="item">
            <i class="fa-solid fa-droplet"></i> {{$member->blood_group}}
        </span>
    </div>
    <div class="id-wrapper">
        <div class="item id">
            <span class="label">Mem. ID</span>
            <span class="value">{{ $member->membership->mid }}</span>
        </div>
        <div class="item">
            <span class="label">Civil ID</span>
            <span class="value">{{ $member->details->civil_id }}</span>
        </div>
        @isset($member->details->member_unit->name)
        <div class="item">
            <span class="label">Unit</span>
            <span class="value">{{ $member->details->member_unit->name }}</span>
        </div>
        @endisset
    </div>
    <div class="logo-wrapper">
        <div class="item logo">
            <img src="{{ url('images/saradhi-logo-v.png') }}" alt="Sarahi Kuwait">
        </div>
        <div class="item qr">
            {{ $member->membership->idQr }}
        </div>
    </div>
    <div class="footer">
        <div class="item">
            <div class="label">MEMBER {{ $member->type == 'primary' ? '('.$member->type.')': ''}}</div>
            <div class="expiry">Expired on {{ date('d M, Y', strtotime($member->membership->expiry_date)) }}</div>
        </div>
        <div class="item">
            <div class="status {{ $member->membership->status }}">{{ $member->membership->status }}</div>
        </div>
    </div>
</div>