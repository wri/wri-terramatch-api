<?php

namespace Tests\V2\ProjectPitches;

use App\Models\V2\ProjectPitch;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_project_pitch(): void
    {
        $user = User::factory()->admin()->create();
        ProjectPitch::factory()->count(2)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/project-pitches/export')
            ->assertStatus(200);
    }

    public function test_user_cannot_export_project_pitch(): void
    {
        $user = User::factory()->create();
        ProjectPitch::factory()->count(2)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/project-pitches/export')
            ->assertStatus(403);
    }
}
