<?php

namespace Modules\Members\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Members\Models\Member;

class MemberExport implements FromCollection, WithHeadings, WithMapping
{
    protected $member;

    public function __construct($member) {
        $this->member = $member;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->member;
    }

    /**
    * @var Member $member
    */
    public function map($member): array
    {
        
        
        return [
            $member->membership->mid,
            $member->name,
            $member->user->email,
            $member->user->phone,
            $member->membership->type,
            $member->details->civil_id,
            $member->details->member_unit->name,
            $member->gender,
            $member->blood_group,
            date('d-m-Y', strtotime($member->details->dob)),
            $member->details->company,
            $member->details->profession,
            $member->details->passport_no,
            date('d-m-Y', strtotime($member->details->passport_expiry)),
            $member->user->avatar,
            
        ];
    }


    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return ['Member ID', 'Name', 'Email', 'Phone', 'Membership Type', 'Civil ID', 'Unit', 'Gender', 'Blood Group', 'DOB', 'Company', 'Profession', 'Passport No', 'Passport Expiry', 'Photo'];
    }
}
