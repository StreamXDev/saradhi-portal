<?php

namespace Modules\PushNotification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PnDelivery extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id','device_id','message_id','sent'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }

    public function device(): HasOne
    {
        return $this->hasOne(PnDevice::class, 'device_id', 'id');
    }

    public function message(): HasOne
    {
        return $this->hasOne(PnMessage::class, 'message_id', 'id');
    }
}
