<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $connection = 'mysql_old';

    protected $with = ['type', 'gender', 'trustee'];

    protected $appends = ['is_trustee', 'mid', 'is_primary'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function details(): HasOne
    {
        return $this->hasOne(MemberDetail::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'membership_id', 'membership_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Enum::class)->select('id', 'code', 'name');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Enum::class)->select('id', 'code', 'name');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function trustee(): HasOne
    {
        return $this->hasOne(Trustee::class);
    }

    public function getIsTrusteeAttribute(): bool
    {
        return $this->trustee !== null;
    }

    public function getIsPrimaryAttribute(): bool
    {
        return $this->type->code === 'primary';
    }

    public function getMidAttribute(): string
    {
        switch ($this->type->code){
            case 'primary':
                $mid = $this->membership->mid;
                break;
            case 'spouse':
                $mid = $this->membership->mid . '-S';
                break;
            case 'child':
                $mid = $this->membership->mid . '-C' . $this->sub_id;
                break;
        }

        return $mid;
    }

}
