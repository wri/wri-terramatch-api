<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSubmissionCsvExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $site = Site::factory()->create();
        SiteSubmission::factory()->count(2)->create(['site_id' => $site->id]);

        $uri = '/api/ppc/export/site/' . $site->id .'/submissions';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertStatus(200);
    }
}
