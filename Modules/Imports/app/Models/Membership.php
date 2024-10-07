<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Membership extends Model
{
    protected $connection = 'mysql_old';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function primary_member(): HasOne
    {
        $primaryTypeId = Enum::where('code', 'primary')->where('type', 'member_type')->first()->id;
        return $this->hasOne(Member::class)->where('type_id', $primaryTypeId);
    }

    public function type(){
        return $this->belongsTo(Enum::class)->select('id', 'code', 'name');
    }

    public function status(){
        return $this->belongsTo(Status::class)->select('id', 'code', 'name');
    }

    public function unit(): HasOne
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
}
