<?php

namespace Tests\V2\Stages;

use App\Models\User;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ViewStageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_stages(): void
    {
        $user = User::factory()->create();
        $stage = Stage::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/funding-programme/stage/' . $stage->uuid)
            ->assertStatus(200);
    }
}
