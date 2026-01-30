<?php
namespace Tests;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Invoice;
use Illuminate\Support\Facades\Hash;
abstract class TenantTestCase extends TestCase
{
    protected Organization $org1;
    protected Organization $org2;
    protected User $admin1;
    protected User $accountant1;
    protected User $admin2;
    protected Vendor $vendor1;
    protected Vendor $vendor2;
    protected Invoice $invoice1;
    protected Invoice $invoice2;
    protected Invoice $invoice3;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }
    protected function setupTestData(): void
    {
        
        $this->org1 = Organization::where('name', 'Test Organization 1')->first();
        $this->org2 = Organization::where('name', 'Test Organization 2')->first();
        
        $this->admin1 = User::where('email', 'admin1@test.com')->first();
        $this->accountant1 = User::where('email', 'accountant1@test.com')->first();
        $this->admin2 = User::where('email', 'admin2@test.com')->first();
        
        $this->vendor1 = Vendor::where('email', 'vendor1@test.com')->first();
        $this->vendor2 = Vendor::where('email', 'vendor2@test.com')->first();
        
        $this->invoice1 = Invoice::where('invoice_number', 'INV-001')->first();
        $this->invoice2 = Invoice::where('invoice_number', 'INV-002')->first();
        $this->invoice3 = Invoice::where('invoice_number', 'INV-003')->first();
    }
    /**
     * Create a user for a specific organization
     */
    protected function createUserForOrganization(Organization $org, string $email, string $role = 'admin'): User
    {
        return User::create([
            'organization_id' => $org->id,
            'name' => 'Test User ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
    /**
     * Create a vendor for a specific organization
     */
    protected function createVendorForOrganization(Organization $org, array $data = []): Vendor
    {
        return Vendor::create(array_merge([
            'organization_id' => $org->id,
            'name' => 'Test Vendor ' . uniqid(),
            'email' => 'vendor' . uniqid() . '@test.com',
            'phone' => '123-456-7890',
            'address' => '123 Test St',
        ], $data));
    }
    /**
     * Create an invoice for a specific organization and vendor
     */
    protected function createInvoiceForOrganization(Organization $org, Vendor $vendor, array $data = []): Invoice
    {
        return Invoice::create(array_merge([
            'organization_id' => $org->id,
            'vendor_id' => $vendor->id,
            'invoice_number' => 'INV-' . uniqid(),
            'amount' => 1000.00,
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'description' => 'Test invoice',
        ], $data));
    }
    /**
     * Assert that a query result only contains records from the specified organization
     */
    protected function assertTenantIsolation($results, Organization $expectedOrg): void
    {
        if ($results instanceof \Illuminate\Database\Eloquent\Collection) {
            foreach ($results as $result) {
                $this->assertEquals($expectedOrg->id, $result->organization_id, 
                    "Record belongs to wrong organization");
            }
        } elseif (is_array($results)) {
            foreach ($results as $result) {
                $this->assertEquals($expectedOrg->id, $result['organization_id'], 
                    "Record belongs to wrong organization");
            }
        }
    }
    /**
     * Assert that a user can only access their own organization's data
     */
    protected function assertUserCanOnlyAccessOwnOrgData(User $user, $queryMethod): void
    {
        $results = $queryMethod($user);
        $this->assertTenantIsolation($results, $user->organization);
    }
    /**
     * Get API token for a user
     */
    protected function getApiTokenForUser(User $user): string
    {
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        return $response->json('token');
    }
    /**
     * Act as a user with API token
     */
    protected function actAsApiUser(User $user): static
    {
        $token = $this->getApiTokenForUser($user);
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}