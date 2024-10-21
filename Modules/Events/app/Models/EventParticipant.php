<?php

namespace Modules\Events\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventParticipant extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id', 'type', 'user_id', 'name', 'company', 'designation', 'unit', 'admitted', 'admitted_on', 'admitted_by'
    ];

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class, 'id','event_id');
    }
    public function user():HasOne
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
    public function admitted_by():HasOne
    {
        return $this->hasOne(User::class, 'id','admitted_by');
    }
}
