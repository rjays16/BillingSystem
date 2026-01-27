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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bureau of Internal Revenue',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Social Security System',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Organization::insert($organizations);
    }
}
