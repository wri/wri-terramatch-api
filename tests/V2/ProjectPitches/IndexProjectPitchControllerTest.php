<?php

namespace Tests\V2\ProjectPitches;

use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IndexProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_project_pitch_index(): void
    {
        $org = Organisation::factory()->create();
        $user = User::factory()->admin()->create([
            'organisation_id' => $org,
        ]);

        ProjectPitch::factory()->count(4)->create([
            'organisation_id' => $org->uuid,
            'status' => ProjectPitch::STATUS_ACTIVE,
            'capacity_building_needs' => ['nursery_management'],
        ]);
        ProjectPitch::factory()->create([
            'organisation_id' => $org->uuid,
            'status' => ProjectPitch::STATUS_DRAFT,
            'project_name' => 'long unique name',
            'capacity_building_needs' => ['nursery_management',],
        ]);
        $thisOne = ProjectPitch::factory()->create([
            'status' => ProjectPitch::STATUS_DRAFT,
            'organisation_id' => $org->uuid,
            'capacity_building_needs' => ['site_selection', 'nursery_management'],
        ]);
        $formSubmission = FormSubmission::factory()->create(['project_pitch_uuid' => $thisOne->uuid, 'status' => FormSubmission::STATUS_STARTED]);

        $newestProjectPitch = ProjectPitch::factory()->create([
            'organisation_id' => $org->uuid,
            'status' => ProjectPitch::STATUS_DRAFT,
            'created_at' => now()->addDecade(),
            'capacity_building_needs' => ['nursery_management'],
        ]);

        // assert a regular index
        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches')
            ->assertStatus(200)
            ->assertJsonCount(7, 'data');

        // assert searching by name
        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?filter[project_name]=long%20unique')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // assert filtering by capacity building needs
        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?filter[capacity_building_needs]=site')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // assert filtering by capacity building needs
        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?filter[status]=draft')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // assert sorting by created at
        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?sort=-created_at')
            ->assertStatus(200)
            ->assertJsonCount(7, 'data')
            ->assertJsonPath('data.0.id', $newestProjectPitch->id);

        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?filter[has_active_application]=true')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?filter[has_active_application]=false')
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');

        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches?filter[no_submissions_for_form]=' . $formSubmission->form_id)
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');
    }

    public function test_users_cannot_view_project_pitches_from_other_organisations_index(): void
    {
        $user = User::factory()->create();
        ProjectPitch::factory()->count(5)->create();
        ProjectPitch::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
