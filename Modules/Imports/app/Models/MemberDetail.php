<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDetail extends Model
{
    protected $connection = 'mysql_old';
    protected $with = ['blood_group'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function blood_group(): BelongsTo
    {
        return $this->belongsTo(Enum::class)->select('id', 'code', 'name');
    }

}
