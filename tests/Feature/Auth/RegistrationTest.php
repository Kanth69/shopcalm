<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        session(['verified_registration_mobile' => '9876543210']);

        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'testcustomer@example.com',
            'mobile_number' => '9876543210',
            'otp' => '123456',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated('customer');
    }
}
