<?php

namespace Modules\Imports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Import extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'imported', 'remark'];

    //User
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
