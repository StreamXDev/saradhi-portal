<?php

namespace Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Members\Database\Factories\MemberRelationFactory;

class MemberRelation extends Model
{

    protected $with = ['relationship'];

    protected $fillable = [
        'member_id',
        'related_member_id',
        'related_dependent_id',
        'relationship_id',
        'active'
    ];

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(MemberEnum::class)->select('id', 'slug', 'name', 'description', 'order');
    }

    public function relatedTo(): HasOne
    {
        return $this->hasOne(Member::class, 'id', 'related_member_id');
    }

}
