<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;

class Enum extends Model
{
    
    protected $connection = 'mysql_old';
    
    public static function allOf($type)
    {
        return self::where('type', $type)->get(['id', 'code', 'name']);
    }

    public static function allActiveOf($type)
    {
        return self::where('type', $type)->where('flag', 1)->get(['id', 'code', 'name']);
    }
}
