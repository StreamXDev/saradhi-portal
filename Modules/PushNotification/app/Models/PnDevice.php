<?php

namespace Modules\PushNotification\Models;

use Illuminate\Database\Eloquent\Model;

class PnDevice extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id','device','os','token','last_active'];
}
