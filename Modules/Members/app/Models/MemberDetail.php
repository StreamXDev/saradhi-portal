<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Members\Database\Factories\MemberDetailFactory;

class MemberDetail extends Model
{
    protected $with = ['member_unit'];
    
    protected $fillable = [
        'user_id',
        'member_unit_id',
        'civil_id',
        'dob',
        'whatsapp',
        'whatsapp_code',
        'emergency_phone',
        'emergency_phone_code',
        'company',
        'profession',
        'company_address',
        'passport_no',
        'passport_expiry',
        'photo_civil_id_front',
        'photo_civil_id_back',
        'photo_passport_front',
        'photo_passport_back',
        'paci',
        'sndp_branch',
        'sndp_branch_number',
        'sndp_union',
        'completed'
    ];

    public function member_unit(): BelongsTo
    {
        return $this->belongsTo(MemberUnit::class)->select('id', 'slug', 'name');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
