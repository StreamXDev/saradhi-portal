<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Members\Database\Factories\MemberRequestFactory;

class MembershipRequest extends Model
{

    use SoftDeletes;
    
    protected $with = ['request_status'];

    protected $fillable = [
        'user_id',
        'request_status_id',
        'updated_by',
        'remark'
    ];

    // Collecting all pending requests
    public function pending(): HasMany
    {
        return $this->hasMany(Member::class, 'user_id', 'user_id');
    }

    public function request_status(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name', 'description', 'order');
    }
}