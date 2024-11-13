<?php

namespace Modules\Events\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Events\Models\EventEnum;

class EventsEnumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enumList = [
            'participant_type' => $this->participantTypes(),
        ];

        foreach ($enumList as $type => $enums) {
            foreach ($enums as $enum) {
                EventEnum::updateOrCreate(
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

    protected function participantTypes()
    {
        return [
            [
                'slug' => 'vip',
                'name' => 'VIP Guest',
                'order' => 0
            ],
            [
                'slug' => 'guest',
                'name' => 'Guest',
                'order' => 1
            ],
            [
                'slug' => 'sponsor',
                'name' => 'Sponsor',
                'order' => 2
            ],
            [
                'slug' => 'member',
                'name' => 'Member',
                'order' => 3
            ],
            [
                'slug' => 'member_dependent',
                'name' => 'Member Dependent',
                'order' => 4
            ],
            [
                'slug' => 'entry_pass',
                'name' => 'Entry Pass',
                'order' => 5
            ],
            [
                'slug' => 'exit_pass',
                'name' => 'Exit Pass',
                'order' => 6
            ],
            
        ];
    }
}
