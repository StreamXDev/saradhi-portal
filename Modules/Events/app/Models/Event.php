<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title','description','start_date','end_date','start_time','end_time','location','thumb','cover'
    ];
}
