<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    protected $connection = 'mysql_old';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function member(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
