<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'login_identifier' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated('customer');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'login_identifier' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('customer');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->actingAs($user, 'customer')->post('/logout');

        $this->assertGuest('customer');
    }
}
