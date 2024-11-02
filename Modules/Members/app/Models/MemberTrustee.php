<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberTrustee extends Model
{
    protected $fillable = ['user_id', 'tid', 'title', 'joining_date', 'status', 'active'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}


