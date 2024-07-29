<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Members\Database\Factories\MemberContactFactory;

class MemberContact extends Model
{
    protected $with = ['contact_type'];

    protected $fillable = ['user_id', 'contact_type_id', 'title', 'value'];

    public function contact_type(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name');
    }
}
