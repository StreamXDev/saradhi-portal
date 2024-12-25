<?php

namespace Modules\Members\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Members\Models\MemberTrustee;

class TrusteesListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $trustee;

    public function __construct($trustee) {
        $this->trustees = $trustee;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->trustees;
    }

    /**
    * @var MemberTrustee $trustee
    */
    public function map($trustee): array
    { 
        return [
            $trustee->user->name,
            $trustee->tid,
            $trustee->member->membership->mid,
            $trustee->status ? 'active' : 'Inactive',
            $trustee->joining_date
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
            'Name',
            'TID',
            'MID',
            'Status',
            'Joining Date'
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
