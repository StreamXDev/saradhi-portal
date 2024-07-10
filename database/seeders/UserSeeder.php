<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::where('email', 'safeeraslamc@gmail.com')->first();
        if (is_null($user)) {
            $user = new User();
            $user->name = "Safeer Aslam";
            $user->email = "safeeraslamc@gmail.com";
            $user->username = 'superadmin';
            $user->password = Hash::make('Com@9900');
            $user->save();
        }
        
    }
}
