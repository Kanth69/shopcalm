<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create(['role_id' => User::ROLE_CUSTOMER]);

        $response = $this
            ->actingAs($user, 'customer')
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['role_id' => User::ROLE_CUSTOMER]);

        $response = $this
            ->actingAs($user, 'customer')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'mobile_number' => '9876543210',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }
}
