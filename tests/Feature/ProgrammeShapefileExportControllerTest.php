<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeShapefileExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->admin()->create();
        $programme = Programme::factory()->create();
        $site = Site::factory()->create([
            'programme_id' => $programme->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/ppc/export/programme/' . $programme->id . '/shapefiles')
            ->assertStatus(200);
    }

    public function test_user_can_export_own()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $programme = Programme::factory()->create(['organisation_id' => $organisation->id]);
        $owner->programmes()->attach($programme->id);

        $site = Site::factory()->create([
            'programme_id' => $programme->id,
        ]);
        $uri = '/api/ppc/export/programme/' . $programme->id . '/shapefiles';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(200);
    }

    public function test_require_admin()
    {
        $user = User::factory()->create();
        $programme = Programme::factory()->create();
        Site::factory()->create([
            'programme_id' => $programme->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/ppc/export/programme/' . $programme->id . '/shapefiles')
            ->assertStatus(403);
    }
}
