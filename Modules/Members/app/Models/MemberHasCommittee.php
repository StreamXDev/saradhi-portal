<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberHasCommittee extends Model
{
    protected $with = ['designation'];

    protected $fillable = ['year'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(MemberCommittee::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name', 'order');
    }
}
