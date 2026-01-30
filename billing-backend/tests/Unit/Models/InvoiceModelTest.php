<?php
namespace Tests\Unit\Models;
use Tests\TenantTestCase;
use App\Models\Invoice;
use App\Models\Vendor;
class InvoiceModelTest extends TenantTestCase
{
    public function it_can_filter_invoices_by_tenant_organization()
    {
        $org1Invoices = Invoice::forTenant($this->org1->id)->get();
        $this->assertCount(2, $org1Invoices);
        $this->assertTenantIsolation($org1Invoices, $this->org1);
    }
    public function it_can_filter_invoices_by_status()
    {
        
        $pendingInvoices = Invoice::forTenant($this->org1->id)
            ->byStatus('pending')
            ->get();
        $this->assertCount(1, $pendingInvoices);
        $this->assertEquals('pending', $pendingInvoices->first()->status);
        $this->assertEquals($this->invoice1->id, $pendingInvoices->first()->id);
    }
    public function it_can_chain_tenant_and_status_scopes()
    {
        
        $paidInvoices = Invoice::forTenant($this->org1->id)
            ->byStatus('paid')
            ->get();
        $this->assertCount(1, $paidInvoices);
        $this->assertEquals('paid', $paidInvoices->first()->status);
        $this->assertEquals($this->invoice2->id, $paidInvoices->first()->id);
    }
    public function it_cannot_access_invoices_from_other_organizations()
    {
        
        $org1Invoices = Invoice::forTenant($this->org1->id)->get();
        
        
        $this->assertNotContains($this->invoice3->id, $org1Invoices->pluck('id'));
    }
    public function it_returns_empty_collection_for_nonexistent_tenant()
    {
        $nonExistentOrgInvoices = Invoice::forTenant(999)->get();
        $this->assertCount(0, $nonExistentOrgInvoices);
    }
    public function it_can_create_invoice_with_tenant_scope()
    {
        $newInvoice = Invoice::create([
            'organization_id' => $this->org1->id,
            'vendor_id' => $this->vendor1->id,
            'invoice_number' => 'INV-NEW-001',
            'amount' => 500.00,
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'New test invoice'
        ]);
        $this->assertEquals($this->org1->id, $newInvoice->organization_id);
        
        
        $org1Invoices = Invoice::forTenant($this->org1->id)->get();
        $this->assertContains($newInvoice->id, $org1Invoices->pluck('id'));
    }
    public function it_maintains_tenant_isolation_in_vendor_relationship()
    {
        
        $invoiceVendor = $this->invoice1->vendor;
        $this->assertEquals($this->org1->id, $invoiceVendor->organization_id);
    }
    public function it_prevents_cross_tenant_invoice_creation()
    {
        
        
        
        
        
        
        $maliciousInvoice = Invoice::make([
            'organization_id' => $this->org1->id,
            'vendor_id' => $this->vendor2->id, 
            'invoice_number' => 'INV-MALICIOUS-001',
            'amount' => 1000.00,
            'status' => 'pending',
        ]);
        
        $this->assertNotEquals($maliciousInvoice->organization_id, $maliciousInvoice->vendor->organization_id);
    }
    public function it_filters_correctly_with_complex_queries()
    {
        
        $anotherInvoice = $this->createInvoiceForOrganization($this->org1, $this->vendor1, [
            'status' => 'pending',
            'amount' => 750.00
        ]);
        
        $complexQuery = Invoice::forTenant($this->org1->id)
            ->byStatus('pending')
            ->where('amount', '>', 600)
            ->get();
        $this->assertCount(1, $complexQuery);
        $this->assertEquals($anotherInvoice->id, $complexQuery->first()->id);
    }
    public function it_handles_null_organization_id_gracefully()
    {
        
        $orphanInvoice = Invoice::create([
            'vendor_id' => $this->vendor1->id,
            'invoice_number' => 'INV-ORPHAN-001',
            'amount' => 100.00,
            'status' => 'pending',
        ]);
        
        $org1Invoices = Invoice::forTenant($this->org1->id)->get();
        $org2Invoices = Invoice::forTenant($this->org2->id)->get();
        $this->assertNotContains($orphanInvoice->id, $org1Invoices->pluck('id'));
        $this->assertNotContains($orphanInvoice->id, $org2Invoices->pluck('id'));
    }
}