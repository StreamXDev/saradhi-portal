<?php

namespace Modules\Members\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Members\Database\Factories\MemberNoteFactory;

class MemberNote extends Model
{

    protected $with = ['createdBy'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'notes',
        'created_by'
    ];
    
    // Collecting User
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // Collecting Author
    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

}
