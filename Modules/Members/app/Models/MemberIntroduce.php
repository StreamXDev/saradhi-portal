<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Members\Database\Factories\MemberIntroduceFactory;

class MemberIntroduce extends Model
{
    
    protected $fillable = [
        'user_id',
        'introducer_id',
        'introducer_name',
        'introducer_phone',
        'introducer_mid',
        'introducer_unit'
    ];
}
