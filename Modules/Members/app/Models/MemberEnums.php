<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberEnums extends Model
{
    use HasFactory, SoftDeletes;

    public static function allOf($type)
    {
        return self::where('type', $type)->get(['id', 'slug', 'name', 'description', 'order']);
    }

    public static function allActiveOf($type)
    {
        return self::where('type', $type)->where('active', 1)->get(['id', 'slug', 'name', 'description', 'order']);
    }
}
