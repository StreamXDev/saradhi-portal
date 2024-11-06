<?php

namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['image','link','order','date','active'];
}
