<?php
namespace Tests\Unit\Models;
use Tests\TenantTestCase;
use App\Models\User;
class UserModelTest extends TenantTestCase
{
    public function it_can_filter_users_by_tenant_organization()
    {
        $org1Users = User::forTenant($this->org1->id)->get();
        
        $this->assertCount(2, $org1Users);
        $this->assertTenantIsolation($org1Users, $this->org1);
    }
    public function it_cannot_access_users_from_other_organizations()
    {
        
        $org1Users = User::forTenant($this->org1->id)->get();
        
        
        $this->assertNotContains($this->admin2->id, $org1Users->pluck('id'));
    }
    public function it_returns_empty_collection_for_nonexistent_tenant()
    {
        $nonExistentOrgUsers = User::forTenant(999)->get();
        $this->assertCount(0, $nonExistentOrgUsers);
    }
    public function it_can_chain_tenant_scope_with_other_filters()
    {
        
        $org1Admins = User::forTenant($this->org1->id)
            ->where('role', 'admin')
            ->get();
        $this->assertCount(1, $org1Admins);
        $this->assertEquals($this->admin1->id, $org1Admins->first()->id);
        $this->assertEquals('admin', $org1Admins->first()->role);
    }
    public function it_can_create_user_with_tenant_scope()
    {
        $newUser = User::create([
            'organization_id' => $this->org1->id,
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'role' => 'accountant'
        ]);
        $this->assertEquals($this->org1->id, $newUser->organization_id);
        
        
        $org1Users = User::forTenant($this->org1->id)->get();
        $this->assertContains($newUser->id, $org1Users->pluck('id'));
    }
    public function it_maintains_tenant_isolation_in_organization_relationship()
    {
        
        $userOrg = $this->admin1->organization;
        $this->assertEquals($this->org1->id, $userOrg->id);
    }
    public function it_filters_users_by_role_within_tenant()
    {
        
        $org1Accountants = User::forTenant($this->org1->id)
            ->where('role', 'accountant')
            ->get();
        $this->assertCount(1, $org1Accountants);
        $this->assertEquals($this->accountant1->id, $org1Accountants->first()->id);
    }
    public function it_prevents_cross_tenant_user_access()
    {
        
        $org1Users = User::forTenant($this->org1->id)
            ->where('id', $this->admin2->id) 
            ->get();
        $this->assertCount(0, $org1Users);
    }
    public function it_handles_multiple_users_per_organization()
    {
        
        $additionalUser1 = $this->createUserForOrganization($this->org1, 'user1@test.com', 'admin');
        $additionalUser2 = $this->createUserForOrganization($this->org1, 'user2@test.com', 'accountant');
        
        $org1Users = User::forTenant($this->org1->id)->get();
        
        $this->assertCount(4, $org1Users);
        $this->assertTenantIsolation($org1Users, $this->org1);
    }
    public function it_can_search_users_by_email_within_tenant()
    {
        
        $searchResult = User::forTenant($this->org1->id)
            ->where('email', 'like', '%admin1%')
            ->get();
        $this->assertCount(1, $searchResult);
        $this->assertEquals($this->admin1->id, $searchResult->first()->id);
    }
    public function it_handles_null_organization_id_gracefully()
    {
        
        $orphanUser = User::create([
            'name' => 'Orphan User',
            'email' => 'orphan@test.com',
            'password' => 'password',
            'role' => 'admin'
        ]);
        
        $org1Users = User::forTenant($this->org1->id)->get();
        $org2Users = User::forTenant($this->org2->id)->get();
        $this->assertNotContains($orphanUser->id, $org1Users->pluck('id'));
        $this->assertNotContains($orphanUser->id, $org2Users->pluck('id'));
    }
}