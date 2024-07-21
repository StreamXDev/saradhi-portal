<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAddress extends Model
{

    protected $fillable = [
        'user_id',
        'type',
        'line_1',
        'line_2',
        'city',
        'country',
        'region',
        'zip'
    ];

}
