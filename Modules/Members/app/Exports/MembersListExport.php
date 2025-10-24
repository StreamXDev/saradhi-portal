<?php

namespace Modules\Members\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Members\Models\Member;

class MembersListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $member;

    public function __construct($member) {
        $this->members = $member;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->members;
    }

    /**
    * @var Member $member
    */
    public function map($member): array
    { 
        $related_member = '';
        foreach($member->relations as $relation){
            if($relation->relatedMember){
                $related_member = ucfirst($relation->relationship->slug).' of: '.$relation->relatedMember->name;
            }
        }
        return [
            $member->user->id+250000,
            $member->membership->mid,
            $member->user->name,
            $member->user->email,
            '+'.$member->user->calling_code.$member->user->phone,
            $member->details->whatsapp ? '+'.$member->details->whatsapp_code.$member->details->whatsapp : '',
            $member->details->emergency_phone ? '+'.$member->details->emergency_phone_code.$member->details->emergency_phone : '',
            $member->membership->family_in == 'kuwait' ? 'Family' : 'Single',
            date('d-m-Y', strtotime($member->membership->start_date)),
            date('d-m-Y', strtotime($member->membership->expiry_date)),
            $member->details->civil_id.' ',
            $member->details->paci,
            $member->details->member_unit->name,
            $member->gender,
            $member->blood_group,
            date('d-m-Y', strtotime($member->details->dob)),
            $member->details->company,
            $member->details->profession,
            $member->details->passport_no,
            date('d-m-Y', strtotime($member->details->passport_expiry)),
            $member->user->avatar ? url('storage/images/'. $member->user->avatar) : '',
            $member->details->sndp_branch,
            $member->details->sndp_branch_number,
            $member->details->sndp_union,
            $member->membership->introducer_name,
            $member->membership->introducer_phone ? '+'.$member->membership->introducer_phone_code.$member->membership->introducer_phone : '',
            $member->membership->introducer_mid,
            $member->membership->introducer_unit,
            $related_member,
            $member->membership->status
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
            'ID',
            'MID', 
            'Name',
            'Email',
            'Phone',
            'Whatsapp',
            'Emergency Phone',
            'Membership Type',
            'Joining Date',
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
            'Introducer Unit',
            'Relation',
            'Status'
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:Z1'; // All headers
                $styleArray = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'DDDDDD']
                    ]
                ];
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray)->getFont()->setSize(14);
            },
        ];
    }
}
