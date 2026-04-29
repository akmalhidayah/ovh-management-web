<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ovh.test'],
            [
                'name' => 'Admin OVH',
                'password' => Hash::make('password'),
                'usertype' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@ovh.test'],
            [
                'name' => 'User OVH',
                'password' => Hash::make('password'),
                'usertype' => 'user',
            ]
        );
    }
}
