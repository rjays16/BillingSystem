<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'Department of Health',
                'code' => 'DOH',
                'description' => 'National government agency responsible for public health policies and programs',
                'address' => 'San Lazaro Compound, Tayuman, Sta. Cruz, Manila 1003',
                'phone' => '+63 2 8651-7800',
                'email' => 'info@doh.gov.ph',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bureau of Internal Revenue',
                'code' => 'BIR',
                'description' => 'National tax collection agency responsible for tax administration',
                'address' => 'BIR National Office, Diliman, Quezon City',
                'phone' => '+63 2 928-0305',
                'email' => 'contactus@bir.gov.ph',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Social Security System',
                'code' => 'SSS',
                'description' => 'Social insurance agency providing social security benefits',
                'address' => 'SSS Building, East Avenue, Diliman, Quezon City',
                'phone' => '+63 2 8920-6401',
                'email' => 'member_relations@sss.gov.ph',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Organization::insert($organizations);
    }
}
