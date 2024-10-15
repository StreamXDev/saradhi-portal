<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberCommittee extends Model
{

    protected $with = ['unit', 'committee_type'];
    
    protected $fillable = [
        'committee_type_id',
        'member_unit_id',
        'formed_on',
        'year',
        'active'
    ];

    public function unit(): HasOne
    {
        return $this->hasOne(MemberUnit::class, 'id' , 'member_unit_id');
    }
    
    public function committee_type(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name', 'order');
    }

    public function committee_members(): HasMany
    {
        return $this->hasMany(MemberHasCommittee::class);
    }
}
