<?php

namespace Tests\Feature;

use App\Models\SiteSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSubmissionAdminCsvExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        SiteSubmission::factory()->count(10)->create();

        $uri = '/api/ppc/export/site/submissions';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertStatus(200);
    }
}
