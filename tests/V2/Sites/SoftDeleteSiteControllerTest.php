<?php

namespace Tests\V2\Sites;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SoftDeleteSiteControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_project_developer_can_soft_delete_sites_without_reports(string $permission, string $fmKey)
    {
        Artisan::call('v2migration:roles');

        $project = Project::factory()->create(['framework_key' => $fmKey]);
        $site = Site::factory()->{$fmKey}()->create([
            'project_id' => $project->id,
        ]);
        $owner = User::factory()->create(['organisation_id' => $project->organisation_id]);
        $owner->givePermissionTo('manage-own');

        $uri = '/api/v2/sites/' . $site->uuid;

        $this->assertFalse($site->trashed());

        $this->actingAs($owner)
            ->delete($uri)
            ->assertSuccessful();

        $site->refresh();

        $this->assertTrue($site->trashed());
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_project_developer_cant_soft_delete_sites_with_reports(string $permission, string $fmKey)
    {
        Artisan::call('v2migration:roles');

        $project = Project::factory()->create(['framework_key' => $fmKey]);
        $site = Site::factory()->{$fmKey}()->create([
            'project_id' => $project->id,
        ]);
        $owner = User::factory()->create(['organisation_id' => $project->organisation_id]);
        $owner->givePermissionTo('manage-own');

        SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => $fmKey,
        ]);

        $uri = '/api/v2/sites/' . $site->uuid;

        $this->assertFalse($site->trashed());

        $this->actingAs($owner)
            ->delete($uri)
            ->assertStatus(406);

        $site->refresh();

        $this->assertFalse($site->trashed());
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
