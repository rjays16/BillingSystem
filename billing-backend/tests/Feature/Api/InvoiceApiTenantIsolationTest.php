<?php
namespace Tests\Feature\Api;
use Tests\TenantTestCase;
use App\Models\User;
use App\Models\Invoice;
class InvoiceApiTenantIsolationTest extends TenantTestCase
{
    public function it_returns_only_tenant_invoices_when_authenticated()
    {
        
        $response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/invoices');
        $response->assertStatus(200);
        $response->assertJsonCount(2); 
        
        $invoiceIds = collect($response->json())->pluck('id');
        $this->assertContains($this->invoice1->id, $invoiceIds);
        $this->assertContains($this->invoice2->id, $invoiceIds);
        $this->assertNotContains($this->invoice3->id, $invoiceIds);
    }
    public function it_can_get_invoices_by_status_within_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/invoices/status/pending');
        $response->assertStatus(200);
        $response->assertJsonCount(1); 
        
        $this->assertEquals($this->invoice1->id, $response->json('0.id'));
        $this->assertEquals('pending', $response->json('0.status'));
    }
    public function it_cannot_access_invoices_from_other_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/invoices');
        $invoiceIds = collect($response->json())->pluck('id');
        
        $this->assertNotContains($this->invoice3->id, $invoiceIds);
    }
    public function it_can_create_invoice_for_own_tenant()
    {
        $invoiceData = [
            'vendor_id' => $this->vendor1->id,
            'number' => 'INV-API-001',
            'amount' => 1500.00,
            'status' => 'pending',
            'date' => now()->toDateString(),
            'description' => 'New API invoice'
        ];
        $response = $this->actAsApiUser($this->admin1)
            ->postJson('/api/invoices', $invoiceData);
        $response->assertStatus(201);
        
        
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
        
        
        $listResponse = $this->actAsApiUser($this->admin1)
            ->getJson('/api/invoices');
        $this->assertGreaterThan(2, $listResponse->json());
    }
    public function it_can_update_invoice_within_own_tenant()
    {
        $updateData = [
            'status' => 'paid',
            'amount' => 2000.00
        ];
        $response = $this->actAsApiUser($this->admin1)
            ->putJson("/api/invoices/{$this->invoice1->id}", $updateData);
        $response->assertStatus(200);
        $this->assertEquals('paid', $response->json('status'));
        $this->assertEquals(2000.00, $response->json('amount'));
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
    }
    public function it_cannot_update_invoice_from_different_tenant()
    {
        $updateData = ['status' => 'cancelled'];
        
        $response = $this->actAsApiUser($this->admin1)
            ->putJson("/api/invoices/{$this->invoice3->id}", $updateData);
        $response->assertStatus(404);
    }
    public function it_can_delete_invoice_within_own_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->deleteJson("/api/invoices/{$this->invoice1->id}");
        $response->assertStatus(204);
        
        
        $getResponse = $this->actAsApiUser($this->admin1)
            ->getJson("/api/invoices/{$this->invoice1->id}");
        $getResponse->assertStatus(404);
    }
    public function it_cannot_delete_invoice_from_different_tenant()
    {
        
        $response = $this->actAsApiUser($this->admin1)
            ->deleteJson("/api/invoices/{$this->invoice3->id}");
        $response->assertStatus(404);
    }
    public function it_can_show_invoice_details_within_own_tenant()
    {
        $response = $this->actAsApiUser($this->admin1)
            ->getJson("/api/invoices/{$this->invoice1->id}");
        $response->assertStatus(200);
        $this->assertEquals($this->invoice1->id, $response->json('id'));
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
    }
    public function it_cannot_show_invoice_details_from_different_tenant()
    {
        
        $response = $this->actAsApiUser($this->admin1)
            ->getJson("/api/invoices/{$this->invoice3->id}");
        $response->assertStatus(404);
    }
    public function different_tenants_see_only_their_own_invoices()
    {
        
        $org1Response = $this->actAsApiUser($this->admin1)
            ->getJson('/api/invoices');
        
        
        $org2Response = $this->actAsApiUser($this->admin2)
            ->getJson('/api/invoices');
        
        $this->assertCount(2, $org1Response->json()); 
        $this->assertCount(1, $org2Response->json()); 
        
        
        $org1Ids = collect($org1Response->json())->pluck('id');
        $org2Ids = collect($org2Response->json())->pluck('id');
        
        $this->assertContains($this->invoice1->id, $org1Ids);
        $this->assertContains($this->invoice2->id, $org1Ids);
        $this->assertContains($this->invoice3->id, $org2Ids);
    }
    public function accountant_can_only_read_invoices_in_own_tenant()
    {
        
        $response = $this->actAsApiUser($this->accountant1)
            ->getJson('/api/invoices');
        $response->assertStatus(200);
        
        $invoiceData = [
            'vendor_id' => $this->vendor1->id,
            'number' => 'INV-TEST',
            'amount' => 1000
        ];
        $response = $this->actAsApiUser($this->accountant1)
            ->postJson('/api/invoices', $invoiceData);
        
        
        
        $this->assertContains($response->status(), [201, 403]);
    }
}