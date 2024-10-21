<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EventRole extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title'
    ];
}
