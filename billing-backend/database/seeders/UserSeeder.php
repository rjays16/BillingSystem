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
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@billing.system',
                'password' => Hash::make('superadmin123'),
                'role' => 'super_admin',
                'organization_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Department of Health Users
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
            [
                'name' => 'DOH Finance Officer',
                'email' => 'finance@doh.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Bureau of Internal Revenue Users
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
            [
                'name' => 'BIR Tax Specialist',
                'email' => 'tax@bir.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Social Security System Users
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
            [
                'name' => 'SSS Branch Manager',
                'email' => 'manager@sss.gov.ph',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        User::insert($users);
    }
}
