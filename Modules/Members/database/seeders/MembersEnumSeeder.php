<?php

namespace Modules\Members\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Members\Models\MemberEnums;

class MembersEnumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enumList = [
            'membership_request_status' => $this->membershipRequestStatuses(),
            'committee_type' => $this->committeeTypes(),
            'designation' => $this->designationTypes(),
        ];

        foreach ($enumList as $type => $enums) {
            foreach ($enums as $enum) {
                MemberEnums::updateOrCreate(
                    [
                        'type' => $type,
                        'slug' => $enum[ 'slug' ]
                    ], [
                        'name' => $enum[ 'name' ],
                        'description' => array_key_exists('description', $enum) ? $enum['description'] : null,
                        'order' => array_key_exists('order', $enum) ? $enum['order'] : 0,
                    ]
                );
            }
        }
    }

    protected function membershipRequestStatuses()
    {
        return [
            [
                'slug' => 'pending',
                'name' => 'Pending',
                'description' => 'Request is waiting for verification',
                'order' => 0
            ],
            [
                'slug' => 'verified',
                'name' => 'Verified',
                'description' => 'Request verification completed. Pending for review',
                'order' => 1
            ],
            [
                'slug' => 'reviewed',
                'name' => 'Reviewed',
                'description' => 'Request review completed. Pending for approval',
                'order' => 2
            ],
            [
                'slug' => 'approved',
                'name' => 'Approved',
                'description' => 'Request is approved',
                'order' => 3
            ],
        ];
    }

    protected function committeeTypes()
    {
        return [
            [
                'slug' => 'central_committee',
                'name' => 'Central Committee',
                'order' => 1
            ],
            [
                'slug' => 'unit_committee',
                'name' => 'Unit Committee',
                'order' => 2
            ],
            [
                'slug' => 'central_vanithavedhi',
                'name' => 'Central Vanithavedhi',
                'order' => 3
            ],
            [
                'slug' => 'unit_vanithavedhi',
                'name' => 'Unit Vanithavedhi',
                'order' => 4
            ],
        ];
    }

    protected function designationTypes()
    {
        return [
            [
                'slug' => 'president',
                'name' => 'President',
                'order' => 1
            ],
            [
                'slug' => 'vise_president',
                'name' => 'Vise President',
                'order' => 2
            ],
            [
                'slug' => 'general_secretary',
                'name' => 'General Secretary',
                'order' => 3
            ],
            [
                'slug' => 'joint_secretary',
                'name' => 'Joint Secretary',
                'order' => 4
            ],
            [
                'slug' => 'treasurer',
                'name' => 'Treasurer',
                'order' => 5
            ],
            [
                'slug' => 'executive_member',
                'name' => 'Executive Member',
                'order' => 6
            ],
            
        ];
    }
}
