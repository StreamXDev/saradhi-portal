<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Members\Database\Factories\MemberRelationFactory;

class MemberRelation extends Model
{

    protected $with = ['relationship'];

    protected $fillable = [
        'user_id',
        'related_user_id',
        'relationship_id',
        'type',
        'active'
    ];

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name');
    }
}
