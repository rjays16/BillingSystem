<?php
namespace Tests\Unit\Models;
use Tests\TenantTestCase;
use App\Models\Vendor;
use App\Models\Invoice;
use App\Models\Organization;
class VendorModelTest extends TenantTestCase
{
    
    public function it_can_filter_vendors_by_tenant_organization()
    {
        
        $org1Vendors = Vendor::forTenant($this->org1->id)->get();
        
        
        $this->assertCount(1, $org1Vendors);
        $this->assertEquals($this->vendor1->id, $org1Vendors->first()->id);
        $this->assertEquals($this->org1->id, $org1Vendors->first()->organization_id);
    }
    
    public function it_cannot_access_vendors_from_other_organizations()
    {
        
        $org1Vendors = Vendor::forTenant($this->org1->id)->get();
        
        
        $this->assertNotContains($this->vendor2->id, $org1Vendors->pluck('id'));
    }
    
    public function it_returns_empty_collection_for_nonexistent_tenant()
    {
        $nonExistentOrgVendors = Vendor::forTenant(999)->get();
        $this->assertCount(0, $nonExistentOrgVendors);
    }
    
    public function it_can_chain_tenant_scope_with_other_scopes()
    {
        
        $newVendor = $this->createVendorForOrganization($this->org1, [
            'name' => 'Another Vendor'
        ]);
        
        $filteredVendors = Vendor::forTenant($this->org1->id)
            ->where('name', 'Another Vendor')
            ->get();
        $this->assertCount(1, $filteredVendors);
        $this->assertEquals($newVendor->id, $filteredVendors->first()->id);
    }
    
    public function it_maintains_tenant_isolation_in_relationships()
    {
        
        $vendorInvoices = $this->vendor1->invoices;
        
        foreach ($vendorInvoices as $invoice) {
            $this->assertEquals($this->org1->id, $invoice->organization_id);
        }
    }
    
    public function it_can_create_vendor_with_tenant_scope()
    {
        $newVendor = Vendor::create([
            'organization_id' => $this->org1->id,
            'name' => 'Test Vendor',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Test St'
        ]);
        $this->assertEquals($this->org1->id, $newVendor->organization_id);
        
        
        $org1Vendors = Vendor::forTenant($this->org1->id)->get();
        $this->assertContains($newVendor->id, $org1Vendors->pluck('id'));
    }
    
    public function it_prevents_cross_tenant_data_access()
    {
        
        $org1Vendors = Vendor::forTenant($this->org1->id)
            ->where('id', $this->vendor2->id) 
            ->get();
        $this->assertCount(0, $org1Vendors);
    }
}