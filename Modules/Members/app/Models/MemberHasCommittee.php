<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberHasCommittee extends Model
{
    protected $with = ['designation', 'user', 'committee'];

    protected $fillable = ['member_committee_id', 'user_id', 'designation_id','active'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'user_id', 'user_id');
    }

    public function committee(): HasOne
    {
        return $this->hasOne(MemberCommittee::class, 'id', 'member_committee_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name', 'order');
    }
}
