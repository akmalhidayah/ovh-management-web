<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::whereIn('email', ['inspector@ovh.test', 'user@ovh.test'])->delete();

        User::updateOrCreate(
            ['email' => 'admin@ovh.test'],
            [
                'name' => 'Admin OVH',
                'password' => Hash::make('password'),
                'phone' => '0411-123456',
                'usertype' => 'admin',
                'role' => 'admin',
                'profile_photo_path' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'qc@ovh.test'],
            [
                'name' => 'User QC',
                'password' => Hash::make('password'),
                'phone' => '0812-1000-0001',
                'usertype' => 'user',
                'role' => 'qc',
                'profile_photo_path' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'commissioning@ovh.test'],
            [
                'name' => 'User Commissioning',
                'password' => Hash::make('password'),
                'phone' => '0812-1000-0002',
                'usertype' => 'user',
                'role' => 'commissioning',
                'profile_photo_path' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'pgo@ovh.test'],
            [
                'name' => 'User PGO',
                'password' => Hash::make('password'),
                'phone' => '0812-1000-0003',
                'usertype' => 'user',
                'role' => 'pgo',
                'profile_photo_path' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'approval@ovh.test'],
            [
                'name' => 'User Approval',
                'password' => Hash::make('password'),
                'phone' => '0812-1000-0004',
                'usertype' => 'user',
                'role' => 'approval',
                'profile_photo_path' => null,
            ]
        );
    }
}
