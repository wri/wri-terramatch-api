<?php

namespace Tests\V2\SiteReports;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
//use Illuminate\Support\Facades\Artisan;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatusSiteReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        //        Artisan::call('v2migration:roles');
        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
        ]);

        $site = Site::factory()->create([
            'framework_key' => 'ppc',
            'project_id' => $project->id,
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => SiteReport::STATUS_AWAITING_APPROVAL,
        ]);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $random = User::factory()->create();
        $random->givePermissionTo('manage-own');

        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

        $payload = ['feedback' => 'testing more info'];
        $uri = '/api/v2/admin/site-reports/' . $report->uuid . '/moreinfo';

        $this->actingAs($random)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment(['status' => SiteReport::STATUS_NEEDS_MORE_INFORMATION]);
    }
}
