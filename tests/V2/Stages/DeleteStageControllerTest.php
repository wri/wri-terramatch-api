<?php

namespace Tests\V2\Stages;

use App\Models\User;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteStageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_delete_stages()
    {
        $user = User::factory()->admin()->create();
        $stage = Stage::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/funding-programme/stage/' . $stage->uuid)
            ->assertStatus(200);
    }

    public function test_non_admins_cannot_update_stages()
    {
        $user = User::factory()->create();
        $stage = Stage::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/funding-programme/stage/' . $stage->uuid)
            ->assertStatus(403);
    }
}
