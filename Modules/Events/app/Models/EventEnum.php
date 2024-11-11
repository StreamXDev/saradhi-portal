<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventEnum extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     */
    public static function allOf($type)
    {
        return self::where('type', $type)->select(['id', 'slug', 'name', 'category', 'description', 'order', 'pass_width_cm', 'pass_height_cm'])->orderBy('order', 'asc')->get();
    }

    public static function allActiveOf($type)
    {
        return self::where('type', $type)->where('active', 1)->select(['id', 'slug', 'name', 'category', 'description', 'order', 'pass_width_cm', 'pass_height_cm'])->orderBy('order', 'asc')->get();
    }
}
