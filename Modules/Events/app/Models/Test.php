<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Events\Database\Factories\TestFactory;

class Test extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['value'];

    protected static function newFactory(): TestFactory
    {
        //return TestFactory::new();
    }
}
