<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vendor;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            // Department of Health Vendors
            [
                'organization_id' => 1,
                'name' => 'Manila Medical Supplies Inc.',
                'email' => 'info@medsupplies.com',
                'phone' => '+63 2 8123-4567',
                'address' => '123 Medical Center, Manila',
                'tax_id' => 'TAX-001-MED',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 1,
                'name' => 'Philippine Pharmaceutical Corp',
                'email' => 'sales@pharma.com',
                'phone' => '+63 2 8234-5678',
                'address' => '456 Drug Ave, Makati',
                'tax_id' => 'TAX-002-PHM',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Bureau of Internal Revenue Vendors
            [
                'organization_id' => 2,
                'name' => 'Office Solutions PH',
                'email' => 'contact@officesolutions.ph',
                'phone' => '+63 2 8345-6789',
                'address' => '789 Business Park, Quezon City',
                'tax_id' => 'TAX-003-OFF',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 2,
                'name' => 'Tech Equipment Services',
                'email' => 'support@techequip.ph',
                'phone' => '+63 2 8456-7890',
                'address' => '101 Tech Hub, Pasig',
                'tax_id' => 'TAX-004-TEC',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Social Security System Vendors
            [
                'organization_id' => 3,
                'name' => 'Document Management Services',
                'email' => 'admin@docmgmt.ph',
                'phone' => '+63 2 8567-8901',
                'address' => '202 Document Center, Mandaluyong',
                'tax_id' => 'TAX-005-DOC',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => 3,
                'name' => 'Facility Maintenance Co.',
                'email' => 'maintenance@facility.com',
                'phone' => '+63 2 8678-9012',
                'address' => '303 Service Road, San Juan',
                'tax_id' => 'TAX-006-FAC',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Vendor::insert($vendors);
    }
}
