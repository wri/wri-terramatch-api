<?php

namespace Tests\V2\SiteReports;

use App\Models\Framework;
use App\Models\V2\sites\SiteReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminIndexSiteReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Framework::factory()->create(['slug' => 'terrafund']);
        Framework::factory()->create(['slug' => 'ppc']);
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();
        $user = User::factory()->create();

        SiteReport::query()->delete();
        SiteReport::factory()->count(3)->create(['framework_key' => 'terrafund']);
        SiteReport::factory()->count(5)->create(['framework_key' => 'ppc']);

        // This will create a soft deleted site that should not appear on the results
        (SiteReport::factory()->create(['framework_key' => 'ppc']))->delete();
        (SiteReport::factory()->create(['framework_key' => 'terrafund']))->delete();

        $uri = '/api/v2/admin/site-reports';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }
}
