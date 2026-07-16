<?php

namespace Modules\Members\Repositories;

use Modules\Members\Models\Member;
use Modules\Members\Models\MemberDetail;
use Modules\Members\Models\MemberLocalAddress;
use Modules\Members\Models\MemberPermanentAddress;
use Modules\Members\Models\Membership;
use Modules\Members\Models\MembershipRequest;

class MemberRepository
{
    public function __construct(
        protected Member $memberModel,
        protected Membership $membershipModel,
        protected MembershipRequest $requestModel,
        protected MemberDetail $memberDetailModel,
        protected MemberLocalAddress $laModel,
        protected MemberPermanentAddress $paModel,
    ){}

    // Update or create member
    public function updateOrCreateMember(int $user_id, array $data)
    {
        return $this->memberModel->updateOrCreate(['user_id', $user_id], $data);
    }

    // Update of create membership
    public function updateOrCreateMembership(int $user_id, array $data)
    {
        return $this->membershipModel->updateOrCreateMembership(['user_id', $user_id], $data);
    }

    // Create request
    public function createRequest(array $data)
    {
        return $this->requestModel->create($data);
    }

    // Update or Create member details
    public function updateOrCreateMemberDetails(int $user_id, array $data)
    {
        return $this->memberDetailModel->updateOrCreate(['user_id', $user_id], $data);
    }

    // Update or Create member details
    public function updateOrCreateLa(int $user_id, array $data)
    {
        return $this->laModel->updateOrCreate(['user_id', $user_id], $data);
    }
    // Update or Create member details
    public function updateOrCreatePa(int $user_id, array $data)
    {
        return $this->paModel->updateOrCreate(['user_id', $user_id], $data);
    }
}
