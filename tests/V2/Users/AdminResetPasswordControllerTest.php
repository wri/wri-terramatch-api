<?php

namespace Tests\V2\Users;

use App\Models\User;
use App\Models\V2\User as V2User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $targetUser = V2User::factory()->create();

        $payload = [
            'password' => 'hb3s&%d8sk!jhY3',
            'password_confirmation' => 'hb3s&%d8sk!jhY3',
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/users/reset-password/' . $targetUser->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/users/reset-password/' . $targetUser->uuid, ['password' => 'meh'])
            ->assertStatus(422);

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/users/reset-password/' . $targetUser->uuid, $payload)
            ->assertStatus(200);

        $fresh = V2User::where('uuid', $targetUser->uuid)->first();
        $this->assertTrue(Hash::check('hb3s&%d8sk!jhY3', $fresh->password));
    }
}
