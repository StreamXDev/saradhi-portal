<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPermanentAddress extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'line_1',
        'line_2',
        'country',
        'region',
        'district',
        'city',
        'zip',
        'contact'
    ];
}
