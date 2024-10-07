<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDependent extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_user_id',
        'name',
        'email',
        'calling_code',
        'phone',
        'avatar',
        'gender',
        'blood_group',
        'civil_id',
        'dob',
        'passport_no',
        'passport_expiry',
        'photo_civil_id_front',
        'photo_civil_id_back',
        'photo_passport_front',
        'photo_passport_back'
    ];

    public function parent_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'parent_user_id');
    }
}
