<?php

namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title', 'body', 'thumb', 'order', 'date', 'active'
    ];
}
