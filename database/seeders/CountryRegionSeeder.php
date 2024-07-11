<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Squire\Models\Country as SquireCountry;
use Squire\Models\Region as SquireRegion;

class CountryRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SquireCountry::all() as $country) {
            $countryInstance = Country::updateOrCreate([
                'code' => $country['code_2'],
            ], [
                'name' => $country['name'],
                'calling_code' => (int) $country['calling_code'],
                'active' => 1
            ]);

            foreach (SquireRegion::where('country_id', $country['code_2'])->get() as $region) {
//                echo "Seeding: ".$region['name']."\n";
                $countryInstance->regions()->updateOrCreate([
                    'code' => $region['code']
                ], [
                    'name' => $region['name']
                ]);
            }
        }
    }
}
