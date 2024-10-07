<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberLocalAddress extends Model
{

    protected $fillable = [
        'user_id',
        'governorate',
        'line_1',
        'building',
        'flat',
        'floor',
        'country',
        'region',
        'district',
        'city',
        'zip'
    ];

}
