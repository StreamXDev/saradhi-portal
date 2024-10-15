<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    protected $connection = 'mysql_old';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['mid', 'member_id', 'membership_id', 'type', 'name', 'exported', 'remark'];

}
