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
            '+'.$member->user->calling_code.$member->user->phone,
            '+'.$member->details->whatsapp_code.$member->details->whatsapp,
            '+'.$member->details->emergency_phone_code.$member->details->emergency_phone,
            $member->membership->type,
            date('d-m-Y', strtotime($member->membership->expiry_date)),
            $member->details->civil_id,
            $member->details->paci,
            $member->details->member_unit->name,
            $member->gender,
            $member->blood_group,
            date('d-m-Y', strtotime($member->details->dob)),
            $member->details->company,
            $member->details->profession,
            $member->details->passport_no,
            date('d-m-Y', strtotime($member->details->passport_expiry)),
            url('storage/images/'. $member->user->avatar),
            $member->details->sndp_branch,
            $member->details->sndp_branch_number,
            $member->details->sndp_union,
            $member->membership->introducer_name,
            '+'.$member->membership->introducer_phone_code.$member->membership->introducer_phone,
            $member->membership->introducer_mid,
            $member->membership->introducer_unit
        ];
    }


    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return [
            'Member ID', 
            'Name',
            'Email',
            'Phone',
            'Whatsapp',
            'Emergency Phone',
            'Membership Type',
            'Membership Expiry',
            'Civil ID',
            'PACI',
            'Unit',
            'Gender',
            'Blood Group',
            'DOB',
            'Company',
            'Profession',
            'Passport No',
            'Passport Expiry',
            'Photo',
            'SNDP Branch',
            'SNDP Branch No.',
            'SNDP Union',
            'Introducer',
            'Introducer Phone',
            'Introducer MID',
            'Introducer Unit'
        ];
    }
}
