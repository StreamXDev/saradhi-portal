<?php

namespace Modules\Events\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Members\Models\MemberDetail;

class EventParticipant extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_id', 'type', 'user_id', 'dependent_id', 'parent_user_id', 'relation', 'name', 'company', 'designation', 'unit', 'pack_count', 'admit_count', 'admitted', 'admitted_on', 'admitted_by', 'created_by'
    ];

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class, 'id','event_id');
    }
    public function user():HasOne
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
    public function member_details():HasOne
    {
        return $this->hasOne(MemberDetail::class, 'user_id', 'user_id');
    }
    public function admittedBy():HasOne
    {
        return $this->hasOne(User::class, 'id','admitted_by');
    }
    public function invitee_type(): BelongsTo
    {
        return $this->belongsTo(EventEnum::class, 'type', 'id')->select('id', 'slug', 'name', 'order', 'category','description', 'pass_width_cm', 'pass_height_cm');
    }
}
