<?php
namespace Tests;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        
        
        
        $this->createTestDatabase();
        
        
        $this->artisan('migrate:fresh', ['--database' => 'mysql']);
        $this->seed(\Database\Seeders\TestDatabaseSeeder::class);
    }
    protected function createTestDatabase()
    {
        
        
        
    }
}
