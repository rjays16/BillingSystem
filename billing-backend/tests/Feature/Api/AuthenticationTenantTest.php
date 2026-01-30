<?php
namespace Tests\Feature\Api;
use Tests\TenantTestCase;
use App\Models\User;
class AuthenticationTenantTest extends TenantTestCase
{
    
    public function login_returns_token_with_correct_tenant_context()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->admin1->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
        
        $userData = $response->json('user');
        $this->assertEquals($this->admin1->id, $userData['id']);
        $this->assertEquals($this->org1->id, $userData['organization_id']);
        $this->assertEquals('admin', $userData['role']);
    }
    public function authenticated_user_can_access_their_own_tenant_data()
    {
        $token = $this->getApiTokenForUser($this->admin1);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
        $response->assertStatus(200);
        $this->assertEquals($this->admin1->id, $response->json('id'));
        $this->assertEquals($this->org1->id, $response->json('organization_id'));
    }
    public function user_cannot_access_api_without_token()
    {
        $response = $this->getJson('/api/vendors');
        $response->assertStatus(401);
        $response = $this->getJson('/api/invoices');
        $response->assertStatus(401);
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }
    public function user_cannot_access_api_with_invalid_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/vendors');
        $response->assertStatus(401);
    }
    public function logout_invalidates_token()
    {
        $token = $this->getApiTokenForUser($this->admin1);
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        $response->assertStatus(200);
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/vendors');
        $response->assertStatus(401);
    }
    public function different_users_from_different_organizations_isolated()
    {
        
        $org1Token = $this->getApiTokenForUser($this->admin1);
        $org1Response = $this->withHeader('Authorization', 'Bearer ' . $org1Token)
            ->getJson('/api/vendors');
        
        $org2Token = $this->getApiTokenForUser($this->admin2);
        $org2Response = $this->withHeader('Authorization', 'Bearer ' . $org2Token)
            ->getJson('/api/vendors');
        
        $org1Response->assertStatus(200);
        $org2Response->assertStatus(200);
        $this->assertCount(1, $org1Response->json());
        $this->assertCount(1, $org2Response->json());
        
        $this->assertEquals($this->vendor1->id, $org1Response->json('0.id'));
        $this->assertEquals($this->vendor2->id, $org2Response->json('0.id'));
    }
    public function login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->admin1->email,
            'password' => 'wrong-password',
        ]);
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials']);
    }
    public function login_fails_for_nonexistent_user()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password',
        ]);
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials']);
    }
    public function authenticated_user_context_preserved_across_requests()
    {
        $token = $this->getApiTokenForUser($this->accountant1);
        $vendorResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/vendors');
        $invoiceResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/invoices');
        $userResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
        
        $vendorResponse->assertStatus(200);
        $invoiceResponse->assertStatus(200);
        $userResponse->assertStatus(200);
        
        $this->assertEquals($this->org1->id, $userResponse->json('organization_id'));
        $this->assertTenantIsolation($vendorResponse->json(), $this->org1);
        $this->assertTenantIsolation($invoiceResponse->json(), $this->org1);
    }
}