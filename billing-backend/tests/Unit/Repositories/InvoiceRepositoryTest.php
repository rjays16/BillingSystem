<?php
namespace Tests\Unit\Repositories;
use Tests\TenantTestCase;
use App\Repositories\InvoiceRepository;
use App\Models\Invoice;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
class InvoiceRepositoryTest extends TenantTestCase
{
    private InvoiceRepository $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(InvoiceRepository::class);
    }
    
    public function it_can_paginate_invoices_for_specific_tenant()
    {
        $invoices = $this->repository->findByTenant($this->org1->id, 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $invoices);
        $this->assertCount(2, $invoices); 
        $this->assertTenantIsolation($invoices->items(), $this->org1);
    }
    
    public function it_returns_empty_pagination_for_tenant_with_no_invoices()
    {
        
        $emptyOrg = $this->createOrganization('Empty Organization');
        
        $invoices = $this->repository->findByTenant($emptyOrg->id, 10);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $invoices);
        $this->assertCount(0, $invoices);
    }
    
    public function it_can_find_invoices_by_tenant_and_status()
    {
        $pendingInvoices = $this->repository->findByTenantAndStatus($this->org1->id, 'pending');
        
        $this->assertCount(1, $pendingInvoices);
        $this->assertEquals($this->invoice1->id, $pendingInvoices->first()->id);
        $this->assertEquals('pending', $pendingInvoices->first()->status);
        $this->assertTenantIsolation($pendingInvoices, $this->org1);
    }
    
    public function it_returns_empty_collection_for_nonexistent_status_in_tenant()
    {
        $invoices = $this->repository->findByTenantAndStatus($this->org1->id, 'cancelled');
        
        $this->assertCount(0, $invoices);
    }
    
    public function it_can_find_invoice_by_number()
    {
        $invoice = $this->repository->findByNumber('INV-001');
        
        $this->assertNotNull($invoice);
        $this->assertEquals('INV-001', $invoice->number);
        $this->assertEquals($this->vendor1->id, $invoice->vendor_id);
    }
    
    public function it_returns_null_when_finding_nonexistent_invoice_number()
    {
        $invoice = $this->repository->findByNumber('INV-NONEXISTENT');
        
        $this->assertNull($invoice);
    }
    
    public function it_can_create_invoice_for_tenant()
    {
        $invoiceData = [
            'vendor_id' => $this->vendor1->id,
            'number' => 'INV-NEW-001',
            'amount' => 1500.00,
            'status' => 'pending',
            'date' => now(),
            'description' => 'New test invoice'
        ];
        $invoice = $this->repository->createForTenant($invoiceData, $this->org1->id);
        $this->assertNotNull($invoice);
        $this->assertEquals($this->org1->id, $invoice->organization_id);
        $this->assertEquals('INV-NEW-001', $invoice->number);
        
        
        $orgInvoices = $this->repository->findByTenant($this->org1->id);
        $this->assertContains($invoice->id, $orgInvoices->pluck('id'));
    }
    
    public function it_can_update_invoice_within_tenant()
    {
        $updateData = [
            'status' => 'paid',
            'amount' => 2000.00
        ];
        $updatedInvoice = $this->repository->updateForTenant(
            $this->invoice1->id, 
            $this->org1->id, 
            $updateData
        );
        $this->assertNotNull($updatedInvoice);
        $this->assertEquals('paid', $updatedInvoice->status);
        $this->assertEquals(2000.00, $updatedInvoice->amount);
        $this->assertEquals($this->org1->id, $updatedInvoice->organization_id);
    }
    
    public function it_throws_exception_when_updating_invoice_from_different_tenant()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $updateData = ['status' => 'cancelled'];
        $this->repository->updateForTenant(
            $this->invoice3->id,  
            $this->org1->id,     
            $updateData
        );
    }
    
    public function it_can_delete_invoice_within_tenant()
    {
        $result = $this->repository->deleteForTenant($this->invoice1->id, $this->org1->id);
        
        $this->assertTrue($result);
        
        
        $deletedInvoice = $this->repository->findById($this->invoice1->id);
        $this->assertNull($deletedInvoice);
    }
    
    public function it_throws_exception_when_deleting_invoice_from_different_tenant()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->deleteForTenant(
            $this->invoice3->id,  
            $this->org1->id       
        );
    }
    
    public function it_includes_vendor_relationship_in_tenant_queries()
    {
        $invoices = $this->repository->findByTenant($this->org1->id);
        
        foreach ($invoices as $invoice) {
            $this->assertTrue($invoice->relationLoaded('vendor'));
            $this->assertNotNull($invoice->vendor);
            $this->assertEquals($invoice->vendor->organization_id, $invoice->organization_id);
        }
    }
    
    public function it_includes_vendor_relationship_in_status_queries()
    {
        $invoices = $this->repository->findByTenantAndStatus($this->org1->id, 'pending');
        
        foreach ($invoices as $invoice) {
            $this->assertTrue($invoice->relationLoaded('vendor'));
            $this->assertNotNull($invoice->vendor);
        }
    }
    
    public function it_prevents_cross_tenant_data_access_with_complex_operations()
    {
        
        $newInvoice1 = $this->createInvoiceForOrganization($this->org1, $this->vendor1);
        $newInvoice2 = $this->createInvoiceForOrganization($this->org2, $this->vendor2);
        
        $org1InvoicesBefore = $this->repository->findByTenant($this->org1->id);
        
        
        $this->repository->updateForTenant($newInvoice1->id, $this->org1->id, [
            'status' => 'paid'
        ]);
        
        $this->repository->deleteForTenant($this->invoice1->id, $this->org1->id);
        
        $org1InvoicesAfter = $this->repository->findByTenant($this->org1->id);
        $this->assertEquals(count($org1InvoicesBefore) - 1, $org1InvoicesAfter->total());
        
        
        $org2Invoices = $this->repository->findByTenant($this->org2->id);
        $this->assertContains($newInvoice2->id, $org2Invoices->pluck('id'));
    }
    
    public function it_maintains_data_integrity_across_tenant_operations()
    {
        
        $initialOrg1Count = $this->repository->findByTenant($this->org1->id)->total();
        $initialOrg2Count = $this->repository->findByTenant($this->org2->id)->total();
        
        $newInvoice1 = $this->createInvoiceForOrganization($this->org1, $this->vendor1);
        
        
        $newInvoice2 = $this->createInvoiceForOrganization($this->org2, $this->vendor2);
        
        $this->repository->updateForTenant($newInvoice1->id, $this->org1->id, [
            'status' => 'paid'
        ]);
        
        $this->repository->deleteForTenant($this->invoice1->id, $this->org1->id);
        
        $finalOrg1Count = $this->repository->findByTenant($this->org1->id)->total();
        $finalOrg2Count = $this->repository->findByTenant($this->org2->id)->total();
        
        $this->assertEquals($initialOrg1Count, $finalOrg1Count);
        
        
        $this->assertEquals($initialOrg2Count + 1, $finalOrg2Count);
    }
    
    public function it_orders_invoices_correctly_in_tenant_queries()
    {
        
        $oldInvoice = $this->createInvoiceForOrganization($this->org1, $this->vendor1, [
            'number' => 'INV-OLD',
            'created_at' => now()->subDays(5)
        ]);
        $newInvoice = $this->createInvoiceForOrganization($this->org1, $this->vendor1, [
            'number' => 'INV-NEW',
            'created_at' => now()->addDays(1)
        ]);
        $invoices = $this->repository->findByTenant($this->org1->id);
        
        
        $this->assertEquals('INV-NEW', $invoices->first()->number);
        $this->assertEquals('INV-OLD', $invoices->last()->number);
    }
    
    private function createOrganization(string $name): \App\Models\Organization
    {
        return \App\Models\Organization::create(['name' => $name]);
    }
}