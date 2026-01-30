<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test user can logout
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test authenticated user can access dashboard
     */
    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test guest cannot access dashboard
     */
    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
