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

    public function test_users_can_authenticate_using_10_digit_mobile_number(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'mobile_number' => '9876543210',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login_identifier' => '9876543210',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated('customer');
    }

    public function test_users_can_authenticate_using_mobile_when_db_has_plus91_prefix(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'mobile_number' => '+919876543210',
            'password' => bcrypt('newpassword123'),
        ]);

        // User enters plain 10 digits
        $response = $this->post('/login', [
            'login_identifier' => '9876543210',
            'password' => 'newpassword123',
        ]);

        $this->assertAuthenticated('customer');
    }

    public function test_users_can_authenticate_using_plus91_when_db_has_10_digits(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'mobile_number' => '9876543210',
            'password' => bcrypt('newpassword123'),
        ]);

        // User enters +91 prefix
        $response = $this->post('/login', [
            'login_identifier' => '+919876543210',
            'password' => 'newpassword123',
        ]);

        $this->assertAuthenticated('customer');
    }

    public function test_users_can_authenticate_using_mobile_with_spaces_or_dashes(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'mobile_number' => '9876543210',
            'password' => bcrypt('newpassword123'),
        ]);

        // User enters mobile with space or dash
        $response = $this->post('/login', [
            'login_identifier' => '+91 98765-43210',
            'password' => 'newpassword123',
        ]);

        $this->assertAuthenticated('customer');
    }

    public function test_users_can_authenticate_with_trailing_space_in_password(): void
    {
        $user = User::factory()->create([
            'role_id' => User::ROLE_CUSTOMER,
            'mobile_number' => '9876543210',
            'password' => bcrypt('secretpassword'),
        ]);

        // Mobile keyboards often append a space
        $response = $this->post('/login', [
            'login_identifier' => '9876543210',
            'password' => 'secretpassword ',
        ]);

        $this->assertAuthenticated('customer');
    }
}
