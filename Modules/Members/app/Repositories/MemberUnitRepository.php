<?php

namespace Modules\Members\Repositories;

use Modules\Members\Models\MemberUnit;

class MemberUnitRepository
{
    
    public function __construct(
        protected MemberUnit $unitModel
    ){}

    public function findBySlug(string $slug)
    {
        return $this->unitModel->where('slug', $slug)->first();
    }
}
