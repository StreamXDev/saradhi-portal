<?php

namespace Modules\Members\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Members\Models\MemberEnum;

class MembersEnumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enumList = [
            'request_status' => $this->membershipRequestStatuses(),
            'committee_type' => $this->committeeTypes(),
            'designation' => $this->designationTypes(),
            'relationship' => $this->relationshipTypes(),
            'contact_type' => $this->contactTypes(),
            'blood_group' => $this->bloodGroups(),
        ];

        foreach ($enumList as $type => $enums) {
            foreach ($enums as $enum) {
                MemberEnum::updateOrCreate(
                    [
                        'type' => $type,
                        'slug' => $enum[ 'slug' ]
                    ], [
                        'name' => $enum[ 'name' ],
                        'category' => array_key_exists('category', $enum) ? $enum['category'] : null,
                        'description' => array_key_exists('description', $enum) ? $enum['description'] : null,
                        'order' => array_key_exists('order', $enum) ? $enum['order'] : 0,
                        'protected' => array_key_exists('protected', $enum) ? $enum['protected'] : 0,
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
                'description' => 'Membership request is waiting for verification',
                'order' => 0,
                'protected' => 1
            ],
            [
                'slug' => 'verified',
                'name' => 'Verified',
                'description' => 'Membership request verification is successfully completed. Waiting for review',
                'order' => 1,
                'protected' => 1
            ],
            [
                'slug' => 'reviewed',
                'name' => 'Reviewed',
                'description' => 'Membership request review is completed. Waiting for approval',
                'order' => 2,
                'protected' => 1
            ],
            [
                'slug' => 'approved',
                'name' => 'Approved',
                'description' => 'Membership request is approved!, Processing data',
                'order' => 3,
                'protected' => 1
            ],
            [
                'slug' => 'confirmed',
                'name' => 'Confirmed',
                'description' => 'Membership ID is issued',
                'order' => 4,
                'protected' => 1
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
                'slug' => 'secretary',
                'name' => 'Secretary',
                'order' => 4
            ],
            [
                'slug' => 'joint_secretary',
                'name' => 'Joint Secretary',
                'order' => 5
            ],
            [
                'slug' => 'treasurer',
                'name' => 'Treasurer',
                'order' => 6
            ],
            [
                'slug' => 'executive_member',
                'name' => 'Executive Member',
                'order' => 7
            ],
            
        ];
    }

    protected function relationshipTypes()
    {
        return [
            [
                'slug' => 'parent',
                'name' => 'Parent',
                'order' => 1
            ],
            [
                'slug' => 'child',
                'name' => 'Child',
                'order' => 2
            ],
            [
                'slug' => 'spouse',
                'name' => 'Spouse',
                'order' => 3
            ],
            [
                'slug' => 'sibling',
                'name' => 'Sibling',
                'order' => 4
            ],
            [
                'slug' => 'grand_parent',
                'name' => 'Grand Parent',
                'order' => 5
            ],
            [
                'slug' => 'in_law',
                'name' => 'In Law',
                'order' => 6
            ],
            [
                'slug' => 'niece',
                'name' => 'Niece',
                'order' => 7
            ],
        ];
    }

    protected function contactTypes()
    {
        return [
            [
                'slug' => 'email',
                'name' => 'Email'
            ],
            [
                'slug' => 'phone',
                'name' => 'Phone'
            ],
            [
                'slug' => 'whatsapp',
                'name' => 'Whatsapp'
            ],
            [
                'slug' => 'facebook',
                'name' => 'Facebook'
            ],
            [
                'slug' => 'instagram',
                'name' => 'Instagram'
            ],
            [
                'slug' => 'x',
                'name' => 'X'
            ],
            [
                'slug' => 'linked_in',
                'name' => 'Linked In'
            ],
        ];
    }

    protected function bloodGroups()
    {
        return [
            [
                'slug' => 'a_p',
                'name' => 'A+'
            ],
            [
                'slug' => 'a_n',
                'name' => 'A-'
            ],
            [
                'slug' => 'b_p',
                'name' => 'B+'
            ],
            [
                'slug' => 'b_n',
                'name' => 'B-'
            ],
            [
                'slug' => 'o_p',
                'name' => 'O+'
            ],
            [
                'slug' => 'o_n',
                'name' => 'O-'
            ],
            [
                'slug' => 'ab_p',
                'name' => 'AB+'
            ],
            [
                'slug' => 'ab_n',
                'name' => 'AB-'
            ],
        ];
    }
}
