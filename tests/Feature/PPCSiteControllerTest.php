<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PPCSiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetSitesByProjectAction(): void
    {
        $admin = User::factory()->admin()->create();

        $project = Programme::factory()->create();
        Site::factory()->count(3)->create(['programme_id' => $project->id]);

        $this->actingAs($admin)
            ->getJson('/api/programme/' . $project->id .'/all-sites')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
