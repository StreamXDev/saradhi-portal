<?php

namespace Modules\Events\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventVolunteer extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id', 'user_id', 'active', 'status', 'added_by', 'added_on'
    ];

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class, 'id', 'event_id');
    }
    public function user():HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
