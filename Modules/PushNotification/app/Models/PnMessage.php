<?php

namespace Modules\PushNotification\Models;

use Illuminate\Database\Eloquent\Model;

class PnMessage extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title','description','image','link','created_by'];
}
