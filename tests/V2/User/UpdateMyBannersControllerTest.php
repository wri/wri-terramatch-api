<?php

namespace Tests\V2\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateMyBannersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_update_their_banners(): void
    {
        $user = User::factory()->admin()->create();

        $payload = [
            'banners' => json_encode([
                'banner 1' => 'yes',
                'banner 2' => 'no',
            ]),
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/my/banners', $payload)
            ->assertStatus(200)
            ->assertJsonFragment($payload);
    }
}
