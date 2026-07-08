<?php

namespace Modules\Members\Repositories;

use Modules\Members\Models\MemberEnum;
use Modules\Members\Models\MembershipRequest;

class MembershipRequestRepository
{
    public function __construct(
        protected MembershipRequest $requestModel,
        protected MemberEnum $enum,
    ){}

    /** 
     * Getting Status Enum by ID 
     */
    public function getStatusEnumById(int $id)
    {
        return $this->enum->where('type', 'request_status')->where('id', $id)->first();
    }

    /** 
     * Getting Status Enum by Slug
     */
    public function getStatusEnumBySlug(string $slug)
    {
        return $this->enum->where('type', 'request_status')->where('slug', $slug)->first();
    }

    /**
     * Getting Status Enum by Order
     */
    public function getStatusEnumByOrder(int $order)
    {
        return $this->enum->where('type', 'request_status')->where('order', $order)->first();
    }

    /**
     * Getting requests by id
     */
    public function getRequestByIds(string $user_id, string $status_id, $checked = false, $single = true)
    {
        $req = $this->requestModel->where('user_id', $user_id)->where('request_status_id', $status_id);
        if($checked){
            $req = $req->where('checked', $checked);
        }
        if($single){
            return $req->first();
        }
        return $req->get();
    }


    /**
     * Creating new request stage
     */
    public function createRequestStage(array $data)
    {
        $requestStage = $this->requestModel->create($data);
        return $requestStage;
    }


}
