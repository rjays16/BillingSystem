<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Invoice;
use Illuminate\Support\Facades\Hash;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test organizations
        $org1 = Organization::create([
            'name' => 'Test Organization 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $org2 = Organization::create([
            'name' => 'Test Organization 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create test users for each organization
        $admin1 = User::create([
            'organization_id' => $org1->id,
            'name' => 'Admin User 1',
            'email' => 'admin1@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $accountant1 = User::create([
            'organization_id' => $org1->id,
            'name' => 'Accountant User 1',
            'email' => 'accountant1@test.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin2 = User::create([
            'organization_id' => $org2->id,
            'name' => 'Admin User 2',
            'email' => 'admin2@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create test vendors for each organization
        $vendor1 = Vendor::create([
            'organization_id' => $org1->id,
            'name' => 'Test Vendor 1',
            'email' => 'vendor1@test.com',
            'phone' => '123-456-7890',
            'address' => '123 Test St, Test City, TS 12345',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $vendor2 = Vendor::create([
            'organization_id' => $org2->id,
            'name' => 'Test Vendor 2',
            'email' => 'vendor2@test.com',
            'phone' => '098-765-4321',
            'address' => '456 Test Ave, Test City, TS 67890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create test invoices for each organization
        Invoice::create([
            'organization_id' => $org1->id,
            'vendor_id' => $vendor1->id,
            'invoice_number' => 'INV-001',
            'amount' => 1000.00,
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Test invoice 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Invoice::create([
            'organization_id' => $org1->id,
            'vendor_id' => $vendor1->id,
            'invoice_number' => 'INV-002',
            'amount' => 2500.00,
            'status' => 'paid',
            'issue_date' => now()->subDays(15),
            'due_date' => now()->addDays(15),
            'description' => 'Test invoice 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Invoice::create([
            'organization_id' => $org2->id,
            'vendor_id' => $vendor2->id,
            'invoice_number' => 'INV-003',
            'amount' => 1500.00,
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Test invoice 3',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}