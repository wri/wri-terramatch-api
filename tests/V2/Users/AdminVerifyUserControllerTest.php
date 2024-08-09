<?php

namespace Tests\V2\Users;

use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminVerifyUserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create([
            'email_address_verified_at' => null,
        ]);

        $this->assertNull($targetUser->email_address_verified_at);

        $this->actingAs($user)
            ->patch('/api/v2/admin/users/verify/' . $targetUser->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->patch('/api/v2/admin/users/verify/' . $targetUser->uuid)
            ->assertStatus(200);

        $targetUser->refresh();
        $this->assertNotNull($targetUser->email_address_verified_at);
    }
}
