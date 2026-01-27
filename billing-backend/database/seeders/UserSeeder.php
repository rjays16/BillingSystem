<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Department of Health - Admin
            [
                'name' => 'DOH Admin',
                'email' => 'admin@doh.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DOH Accountant',
                'email' => 'accountant@doh.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'organization_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Bureau of Internal Revenue - Admin
            [
                'name' => 'BIR Admin',
                'email' => 'admin@bir.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BIR Accountant',
                'email' => 'accountant@bir.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Social Security System - Admin
            [
                'name' => 'SSS Admin',
                'email' => 'admin@sss.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SSS Accountant',
                'email' => 'accountant@sss.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'organization_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        User::insert($users);
    }
}
