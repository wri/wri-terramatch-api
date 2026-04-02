<?php

namespace Tests\V2\Auth;

use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_resend_by_email_action(): void
    {
        $user = User::factory()->create(['locale' => 'en-US']);
        $this->actingAs($user);

        $this->postJson('/api/v2/users/resend', [
            'email_address' => $user->email_address,
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
        $this->assertDatabaseHas('verifications', ['user_id' => $user->id]);
    }
}
