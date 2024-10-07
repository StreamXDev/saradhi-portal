<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $connection = 'mysql_old';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
}
