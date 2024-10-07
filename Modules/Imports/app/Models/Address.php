<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    protected $connection = 'mysql_old';
    protected $with = ['type'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'code', 'country_code');
    }

    public function region(): HasOne
    {
        return $this->hasOne(CountryRegion::class, 'code', 'region_code');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Enum::class)->select('id', 'code', 'name');
    }
}
