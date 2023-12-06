<?php

namespace Tests\V2\ProjectPitches;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminIndexProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_view_project_pitch_index(): void
    {
        $user = User::factory()->admin()->create();

        ProjectPitch::factory()->count(5)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/project-pitches')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_admins_cannot_view_project_pitch_index(): void
    {
        $user = User::factory()->create();
        ProjectPitch::factory()->count(5)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/project-pitches')
            ->assertStatus(403);
    }

    public function test_status_filtering(): void
    {
        $admin = User::factory()->admin()->create();
        ProjectPitch::factory()->count(5)->create(['status' => ProjectPitch::STATUS_DRAFT]);
        ProjectPitch::factory()->count(4)->create(['status' => ProjectPitch::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/project-pitches?filter[status]=' . ProjectPitch::STATUS_DRAFT  . '&sort=-total_trees')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');
    }

    public function test_organisation_filtering()
    {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();
        ProjectPitch::factory()->count(5)->create(['organisation_id' => $organisation->uuid]);
        ProjectPitch::factory()->count(4)->create();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/project-pitches?filter[organisation_id]=' . $organisation->uuid)
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');
    }
}
