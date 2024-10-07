<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $connection = 'mysql_old';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function regions(): HasMany
    {
        return $this->hasMany(CountryRegion::class, 'country_code', 'code');
    }
}
