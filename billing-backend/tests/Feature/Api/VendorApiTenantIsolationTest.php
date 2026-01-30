<?php
namespace Tests\Feature\Api;
use Tests\TenantTestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Invoice;
class VendorApiTenantIsolationTest extends TenantTestCase
{
    public function it_returns_only_tenant_vendors_when_authenticated()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/vendors');
        $response->assertStatus(200);
        $response->assertJsonCount(1); 
        
        $vendorData = $response->json('0');
        $this->assertEquals($this->vendor1->id, $vendorData['id']);
        $this->assertEquals($this->org1->id, $vendorData['organization_id']);
    }
    public function it_cannot_access_vendors_from_other_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/vendors');
        $response->assertStatus(200);
        $vendorIds = collect($response->json())->pluck('id');
        
        $this->assertNotContains($this->vendor2->id, $vendorIds);
    }
    public function it_can_create_vendor_for_own_tenant()
    {
        $vendorData = [
            'name' => 'New API Vendor',
            'email' => 'newapi@test.com',
            'phone' => '123-456-7890',
            'address' => '123 API St'
        ];
        $response = $this->actAsApiUser($this->admin1)
            ->postJson('/api/vendors', $vendorData);
        $response->assertStatus(201);
        
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
        
        $listResponse = $this->actAsApiUser($this->admin1)
            ->getJson('/api/vendors');
        $this->assertGreaterThan(1, $listResponse->json());
    }
    public function it_can_update_vendor_within_own_tenant()
    {
        $updateData = [
            'name' => 'Updated API Vendor',
            'email' => 'updated@test.com'
        ];
        $response = $this->actAsApiUser($this->admin1)
            ->putJson("/api/vendors/{$this->vendor1->id}", $updateData);
        $response->assertStatus(200);
        $this->assertEquals('Updated API Vendor', $response->json('name'));
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
    }
    public function it_cannot_update_vendor_from_different_tenant()
    {
        $updateData = ['name' => 'Should Not Update'];
        
        $response = $this->actAsApiUser($this->admin1)
            ->putJson("/api/vendors/{$this->vendor2->id}", $updateData);
        $response->assertStatus(404);
    }
    public function it_can_delete_vendor_within_own_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->deleteJson("/api/vendors/{$this->vendor1->id}");
        $response->assertStatus(204);
        $getResponse = $this->actAsApiUser($this->admin1)
            ->getJson("/api/vendors/{$this->vendor1->id}");
        $getResponse->assertStatus(404);
    }
    public function it_cannot_delete_vendor_from_different_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->deleteJson("/api/vendors/{$this->vendor2->id}");
        $response->assertStatus(404);
    }
    public function it_can_show_vendor_details_within_own_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->getJson("/api/vendors/{$this->vendor1->id}");
        $response->assertStatus(200);
        $this->assertEquals($this->vendor1->id, $response->json('id'));
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
    }
    public function it_cannot_show_vendor_details_from_different_tenant()
    {
        
        $response = $this->actAsApiUser($this->admin1)
            ->getJson("/api/vendors/{$this->vendor2->id}");
        $response->assertStatus(404);
    }
    public function different_tenants_see_only_their_own_vendors()
    {
        $org1Response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/vendors');
    
        $org2Response = $this->actAsApiUser($this->admin2)
            ->getJson('/api/vendors');
        $this->assertCount(1, $org1Response->json());
        $this->assertCount(1, $org2Response->json());
        
        $this->assertEquals($this->vendor1->id, $org1Response->json('0.id'));
        $this->assertEquals($this->vendor2->id, $org2Response->json('0.id'));
    }
    public function accountant_role_can_only_read_vendors_in_own_tenant()
    {
        $response = $this->actAsApiUser($this->accountant1)
            ->getJson('/api/vendors');
        $response->assertStatus(200);
        $vendorData = ['name' => 'Test Vendor'];
        $response = $this->actAsApiUser($this->accountant1)
            ->postJson('/api/vendors', $vendorData);
        
        $this->assertContains($response->status(), [201, 403]);
    }
}