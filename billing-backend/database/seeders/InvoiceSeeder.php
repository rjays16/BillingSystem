<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = [
            // Department of Health Invoices
            [
                'organization_id' => 1,
                'vendor_id' => 1,
                'number' => 'DOH-2024-001',
                'amount' => 150000.00,
                'status' => 'paid',
                'date' => '2024-01-15',
                'due_date' => '2024-02-15',
                'notes' => 'Medical supplies for Q1 2024',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 1,
                'vendor_id' => 2,
                'number' => 'DOH-2024-002',
                'amount' => 275000.50,
                'status' => 'sent',
                'date' => '2024-01-20',
                'due_date' => '2024-02-20',
                'notes' => 'Pharmaceutical supplies for Manila hospitals',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 1,
                'vendor_id' => 1,
                'number' => 'DOH-2024-003',
                'amount' => 89000.75,
                'status' => 'overdue',
                'date' => '2023-12-10',
                'due_date' => '2024-01-10',
                'notes' => 'Emergency medical equipment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 1,
                'vendor_id' => 2,
                'number' => 'DOH-2024-004',
                'amount' => 45000.25,
                'status' => 'cancelled',
                'date' => '2024-01-25',
                'due_date' => '2024-02-25',
                'notes' => 'Cancelled hospital equipment order',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Bureau of Internal Revenue Invoices
            [
                'organization_id' => 2,
                'vendor_id' => 3,
                'number' => 'BIR-2024-001',
                'amount' => 45000.00,
                'status' => 'paid',
                'date' => '2024-01-05',
                'due_date' => '2024-02-05',
                'notes' => 'Office furniture for BIR main office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 2,
                'vendor_id' => 4,
                'number' => 'BIR-2024-002',
                'amount' => 320000.00,
                'status' => 'cancelled',
                'date' => '2024-01-25',
                'due_date' => '2024-02-25',
                'notes' => 'Computer equipment upgrade - cancelled due to budget constraints',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 2,
                'vendor_id' => 3,
                'number' => 'BIR-2024-003',
                'amount' => 125000.00,
                'status' => 'sent',
                'date' => '2024-01-28',
                'due_date' => '2024-02-28',
                'notes' => 'Tax consulting services for Q1 2024',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Social Security System Invoices
            [
                'organization_id' => 3,
                'vendor_id' => 5,
                'number' => 'SSS-2024-001',
                'amount' => 67000.00,
                'status' => 'sent',
                'date' => '2024-01-18',
                'due_date' => '2024-02-18',
                'notes' => 'Document management system license',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 3,
                'vendor_id' => 6,
                'number' => 'SSS-2024-002',
                'amount' => 125000.00,
                'status' => 'overdue',
                'date' => '2024-01-10',
                'due_date' => '2024-02-10',
                'notes' => 'Facility maintenance services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 3,
                'vendor_id' => 5,
                'number' => 'SSS-2024-003',
                'amount' => 89000.50,
                'status' => 'paid',
                'date' => '2024-01-15',
                'due_date' => '2024-02-15',
                'notes' => 'IT infrastructure upgrade for SSS branches',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 3,
                'vendor_id' => 6,
                'number' => 'SSS-2024-004',
                'amount' => 45000.75,
                'status' => 'cancelled',
                'date' => '2024-01-30',
                'due_date' => '2024-02-28',
                'notes' => 'Cancelled facility renovation project',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Invoice::insert($invoices);
    }
}
