<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryRegion extends Model
{
    protected $connection = 'mysql_old';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}
