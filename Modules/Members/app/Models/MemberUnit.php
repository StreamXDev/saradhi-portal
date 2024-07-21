<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberUnit extends Model
{
    
    protected $fillable = ['slug', 'name', 'active'];

    public function committee(): HasOne
    {
        return $this->hasOne(MemberCommittee::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
