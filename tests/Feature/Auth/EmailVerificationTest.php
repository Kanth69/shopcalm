<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_customer_can_access_account(): void
    {
        $user = User::factory()->create(['role_id' => User::ROLE_CUSTOMER]);

        $response = $this->actingAs($user, 'customer')->get('/account/orders');

        $response->assertStatus(200);
    }
}
