<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Members\Database\Factories\MemberRequestFactory;

class MembershipRequest extends Model
{

    use SoftDeletes;
    
    protected $with = ['request_status'];

    protected $fillable = [
        'user_id',
        'request_status_id',
        'checked',
        'rejected',
        'updated_by',
        'remark'
    ];

    // Collecting User
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // Collecting User
    public function updatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    // Collecting member
    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'user_id', 'user_id');
    }

    // member details
    public function details(): HasOne
    {
        return $this->hasOne(MemberDetail::class, 'user_id', 'user_id');
    }

    // request status details
    public function request_status(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name', 'description', 'order');
    }
}