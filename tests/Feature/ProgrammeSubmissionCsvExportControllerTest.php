<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeSubmissionCsvExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_programme_requires_admin()
    {
        $user = User::factory()->create();
        $programme = Programme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/ppc/export/programme/' . $programme->id .'/submissions')
            ->assertStatus(403);
    }

    public function test_my_programme_submissions_action()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $programme = Programme::factory()->create(['organisation_id' => $organisation->id]);
        $owner->programmes()->attach($programme->id);

        Submission::factory()->count(3)->create(['programme_id' => $programme->id]);
        $sites = Site::factory()->count(3)->create(['programme_id' => $programme->id]);
        foreach ($sites as $site) {
            SiteSubmission::factory()->count(2)->create(['site_id' => $site->id]);
        }

        $this->actingAs($owner)
            ->getJson('/api/ppc/export/programme/' . $programme->id .'/submissions')
            ->assertStatus(200);
    }
}
