<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create organizations first
        Organization::create([
            'name' => 'City Government Office',
        ]);

        Organization::create([
            'name' => 'State Department',
        ]);

        Organization::create([
            'name' => 'Federal Agency',
        ]);

        // Create users with proper email domains
        $organizations = Organization::all();

        $users = [
            [
                'name' => 'John Admin',
                'email' => 'admin@sample.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_name' => 'City Government Office',
            ],
            [
                'name' => 'Jane Accountant',
                'email' => 'accountant@sample.com', 
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'organization_name' => 'City Government Office',
            ],
            [
                'name' => 'Bob Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_name' => 'State Department',
            ],
            [
                'name' => 'Alice Director',
                'email' => 'alice@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_name' => 'State Department',
            ],
            [
                'name' => 'Charlie Clerk',
                'email' => 'charlie@allan.com',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'organization_name' => 'Federal Agency',
            ],
            [
                'name' => 'Diana Supervisor',
                'email' => 'diana@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'organization_name' => 'Federal Agency',
            ],
        ];

        foreach ($users as $userData) {
            $organization = $organizations->where('name', $userData['organization_name'])->first();
            
            if ($organization) {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'role' => $userData['role'],
                    'organization_id' => $organization->id,
                ]);
            }
        }
    }
}
