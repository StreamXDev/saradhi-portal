<?php

namespace Modules\Members\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Members\Models\MemberUnit;

class MemberUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $units = [
            [
                'slug' => 'abbassiyaeast',
                'name' => 'Abbassiya East',
            ],
            [
                'slug' => 'abbassiyawest',
                'name' => 'Abbassiya West',
            ],
            [
                'slug' => 'abuhalifa',
                'name' => 'Abuhalifa',
            ],
            [
                'slug' => 'ahmadi',
                'name' => 'Ahmadi',
            ],
            [
                'slug' => 'fahaheel',
                'name' => 'Fahaheel',
            ],
            [
                'slug' => 'farwaniya',
                'name' => 'Farwaniya',
            ],
            [
                'slug' => 'hassawinorth',
                'name' => 'Hassawi North',
            ],
            [
                'slug' => 'hassawisouth',
                'name' => 'Hassawi South',
            ],
            [
                'slug' => 'hassawieast',
                'name' => 'Hassawi East',
            ],
            [
                'slug' => 'hawally',
                'name' => 'Hawally',
            ],
            [
                'slug' => 'jahra',
                'name' => 'Jahra',
            ],
            [
                'slug' => 'mangafeast',
                'name' => 'Mangaf East',
            ],
            [
                'slug' => 'mangafwest',
                'name' => 'Mangaf West',
            ],
            [
                'slug' => 'reggai',
                'name' => 'Reggai',
            ],
            [
                'slug' => 'salmiya',
                'name' => 'Salmiya',
            ],
            [
                'slug' => 'saradhitrust',
                'name' => 'Saradhi Trust',
            ],
            [
                'slug' => 'mehboula',
                'name' => 'Mehboula',
            ],
        ];

        foreach ($units as $unit){
            if (MemberUnit::where('slug', $unit['slug'])->count() < 1){
                MemberUnit::create($unit);
            }
        }
    }
}