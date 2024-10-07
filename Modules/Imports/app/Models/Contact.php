<?php

namespace Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Imports\Database\Factories\ContactFactory;

class Contact extends Model
{
    protected $connection = 'mysql_old';
    
    protected $with = ['type'];
    
    protected $fillable = [];

    public function type(){
        return $this->belongsTo(Enum::class)->select('id', 'code', 'name');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
