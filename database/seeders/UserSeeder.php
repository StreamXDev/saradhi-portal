<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Members\Models\Member;
use Nwidart\Modules\Facades\Module;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::where('email', 'shanoob.sekhar@gmail.com')->first();
        if (is_null($user)) {
            $user = new User();
            $user->name = "Shanoob Sekhar";
            $user->email = "shanoob.sekhar@gmail.com";
            $user->username = 'superadmin';
            $user->password = Hash::make('abc@123');
            $user->email_verified_at = now();
            $user->save();
        }

        if(Module::has('Members')){
            $member = Member::where('user_id', $user->id)->first();
            if(is_null($member)){
                $member = new Member();
                $member->name = 'Shanoob Sekhar';
                $member->user_id = $user->id;
                $member->save();
            }
        }
        
    }
}
