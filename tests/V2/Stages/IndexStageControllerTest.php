<?php

namespace Tests\V2\Stages;

use App\Models\V2\Stages\Stage;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IndexStageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_stage_index(): void
    {
        $user = User::factory()->create();

        Stage::factory()->count(5)->create();
        $count = Stage::count();

        // it's paginated to 100
        if ($count > 100) {
            $count = 100;
        }

        $this->actingAs($user)
            ->getJson('/api/v2/funding-programme/stage?per_page=100')
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }
}
