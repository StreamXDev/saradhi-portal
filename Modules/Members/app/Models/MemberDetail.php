<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Members\Database\Factories\MemberDetailFactory;

class MemberDetail extends Model
{
    protected $with = ['member_unit'];
    
    protected $fillable = [
        'user_id',
        'member_unit_id',
        'civil_id',
        'dob',
        'company',
        'profession',
        'passport_no',
        'passport_expiry',
        'photo_civil_id_front',
        'photo_civil_id_back',
        'photo_passport_front',
        'photo_passport_back',
        'completed'
    ];

    public function member_unit(): BelongsTo
    {
        return $this->belongsTo(MemberUnit::class)->select('id', 'slug', 'name');
    }
}
