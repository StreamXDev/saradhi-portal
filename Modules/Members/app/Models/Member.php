<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{

    protected $appends = ['is_trustee'];

    protected $fillable = [
        'user_id',
        'parent_id',
        'type',
        'civil_id',
        'name',
        'gender',
        'dob',
        'company',
        'profession',
        'passport_no',
        'passport_expiry',
        'blood_group',
        'photo',  
    ];

    //User
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    // Member details
    public function details(): HasOne
    {
        return $this->hasOne(MemberDetail::class, 'user_id', 'user_id');
    }

    // Membership details
    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class, 'user_id', 'user_id');
    }

    // Member contacts
    public function contacts(): HasMany
    {
        return $this->hasMany(MemberContact::class, 'user_id', 'user_id');
    }

    // Member addresses
    public function addresses(): HasMany
    {
        return $this->hasMany(MemberAddress::class, 'user_id', 'user_id');
    }
    
    public function relations(): HasMany
    {
        return $this->hasMany(MemberRelation::class, 'user_id', 'user_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MembershipRequest::class, 'user_id', 'user_id');
    }

    public function committees(): HasMany
    {
        return $this->hasMany(MemberHasCommittee::class, 'user_id', 'user_id');
    }

    public function trustee(): HasOne
    {
        return $this->hasOne(MemberTrustee::class, 'user_id', 'user_id');
    }

    public function getIsTrusteeAttribute(): bool
    {
        return $this->trustee !== null;
    }

}
