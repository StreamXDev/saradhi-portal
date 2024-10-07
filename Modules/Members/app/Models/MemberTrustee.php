<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTrustee extends Model
{
    protected $fillable = ['user_id', 'tid', 'title', 'joining_date', 'status', 'active'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }
}
