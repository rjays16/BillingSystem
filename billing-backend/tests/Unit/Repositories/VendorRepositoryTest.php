<?php
namespace Tests\Unit\Repositories;
use Tests\TenantTestCase;
use App\Repositories\VendorRepository;
use App\Models\Vendor;
use App\Models\Organization;
class VendorRepositoryTest extends TenantTestCase
{
    private VendorRepository $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(VendorRepository::class);
    }
    public function it_can_get_all_vendors_for_specific_tenant()
    {
        $vendors = $this->repository->forTenant($this->org1->id);
        
        $this->assertCount(1, $vendors);
        $this->assertEquals($this->vendor1->id, $vendors->first()->id);
        $this->assertTenantIsolation($vendors, $this->org1);
    }
    public function it_returns_empty_collection_for_tenant_with_no_vendors()
    {
        $emptyOrg = $this->createOrganization('Empty Organization');
        
        $vendors = $this->repository->forTenant($emptyOrg->id);
        
        $this->assertCount(0, $vendors);
    }
    public function it_can_find_vendor_by_id_within_tenant()
    {
        $vendor = $this->repository->findByIdForTenant($this->vendor1->id, $this->org1->id);
        
        $this->assertNotNull($vendor);
        $this->assertEquals($this->vendor1->id, $vendor->id);
        $this->assertEquals($this->org1->id, $vendor->organization_id);
    }
    public function it_returns_null_when_finding_vendor_from_different_tenant()
    {
        $vendor = $this->repository->findByIdForTenant($this->vendor2->id, $this->org1->id);
        
        $this->assertNull($vendor);
    }
    public function it_returns_null_when_finding_nonexistent_vendor_for_tenant()
    {
        $vendor = $this->repository->findByIdForTenant(999, $this->org1->id);
        
        $this->assertNull($vendor);
    }
    public function it_can_create_vendor_for_tenant()
    {
        $vendorData = [
            'organization_id' => $this->org1->id,
            'name' => 'New Vendor',
            'email' => 'newvendor@test.com',
            'phone' => '123-456-7890',
            'address' => '123 New St'
        ];
        $vendor = $this->repository->create($vendorData);
        $this->assertNotNull($vendor);
        $this->assertEquals($this->org1->id, $vendor->organization_id);
        
        $orgVendors = $this->repository->forTenant($this->org1->id);
        $this->assertContains($vendor->id, $orgVendors->pluck('id'));
    }
    public function it_can_update_vendor_within_tenant()
    {
        $updateData = [
            'name' => 'Updated Vendor Name',
            'email' => 'updated@test.com'
        ];
        $updatedVendor = $this->repository->updateForTenant(
            $this->vendor1->id, 
            $this->org1->id, 
            $updateData
        );
        $this->assertNotNull($updatedVendor);
        $this->assertEquals('Updated Vendor Name', $updatedVendor->name);
        $this->assertEquals('updated@test.com', $updatedVendor->email);
        $this->assertEquals($this->org1->id, $updatedVendor->organization_id);
    }
    public function it_returns_null_when_updating_vendor_from_different_tenant()
    {
        $updateData = ['name' => 'Should Not Update'];
        $result = $this->repository->updateForTenant(
            $this->vendor2->id, 
            $this->org1->id, 
            $updateData
        );
        $this->assertNull($result);
        
        $originalVendor = $this->repository->findById($this->vendor2->id);
        $this->assertNotEquals('Should Not Update', $originalVendor->name);
    }
    public function it_can_delete_vendor_within_tenant()
    {
        $result = $this->repository->deleteForTenant($this->vendor1->id, $this->org1->id);
        
        $this->assertTrue($result);
        
        $deletedVendor = $this->repository->findById($this->vendor1->id);
        $this->assertNull($deletedVendor);
    }
    public function it_returns_false_when_deleting_vendor_from_different_tenant()
    {
        $result = $this->repository->deleteForTenant($this->vendor2->id, $this->org1->id);
        
        $this->assertFalse($result);
        
        $existingVendor = $this->repository->findById($this->vendor2->id);
        $this->assertNotNull($existingVendor);
    }
    public function it_prevents_cross_tenant_data_access_with_multiple_operations()
    {
        $newVendor1 = $this->createVendorForOrganization($this->org1);
        $newVendor2 = $this->createVendorForOrganization($this->org2);
        $org1VendorsBefore = $this->repository->forTenant($this->org1->id);
        
        $this->repository->updateForTenant($newVendor1->id, $this->org1->id, [
            'name' => 'Updated Org1 Vendor'
        ]);
        $this->repository->deleteForTenant($this->vendor1->id, $this->org1->id);
        $org1VendorsAfter = $this->repository->forTenant($this->org1->id);
        $this->assertCount(count($org1VendorsBefore) - 1, $org1VendorsAfter);
        
        $org2Vendors = $this->repository->forTenant($this->org2->id);
        $this->assertContains($newVendor2->id, $org2Vendors->pluck('id'));
    }
    public function it_handles_tenant_isolation_with_pagination()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createVendorForOrganization($this->org1, [
                'name' => "Vendor {$i}"
            ]);
        }
        $vendors = $this->repository->forTenant($this->org1->id);
        $this->assertGreaterThan(5, $vendors->count());
        $this->assertTenantIsolation($vendors, $this->org1);
    }
    public function it_maintains_data_integrity_across_tenant_operations()
    {
        $initialOrg1Count = $this->repository->forTenant($this->org1->id)->count();
        $initialOrg2Count = $this->repository->forTenant($this->org2->id)->count();
        $newVendor1 = $this->createVendorForOrganization($this->org1);
        
        $newVendor2 = $this->createVendorForOrganization($this->org2);
        $this->repository->updateForTenant($newVendor1->id, $this->org1->id, [
            'name' => 'Updated Name'
        ]);
        $this->repository->deleteForTenant($this->vendor1->id, $this->org1->id);
        $finalOrg1Count = $this->repository->forTenant($this->org1->id)->count();
        $finalOrg2Count = $this->repository->forTenant($this->org2->id)->count();
        $this->assertEquals($initialOrg1Count, $finalOrg1Count);
        
        $this->assertEquals($initialOrg2Count + 1, $finalOrg2Count);
    }
    private function createOrganization(string $name): Organization
    {
        return Organization::create(['name' => $name]);
    }
}