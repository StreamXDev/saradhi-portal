<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'start_date',
        'updated_date',
        'expiry_date',
        'type',
        'status',
        'joined_as',
        'introducer_name',
        'introducer_phone',
        'introducer_mid',
        'introducer_unit',
    ];

    public function members(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }


}
