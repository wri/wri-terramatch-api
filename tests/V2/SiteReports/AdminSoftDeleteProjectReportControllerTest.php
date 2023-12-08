<?php

namespace SiteReports;

use App\Models\User;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminSoftDeleteProjectReportControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_users_cant_soft_delete_project_reports(string $permission, string $fmKey)
    {
        $user = User::factory()->create();

        $project = Project::factory()->{$fmKey}()->create();
        $report = ProjectReport::factory()->{$fmKey}()->for($project)->create();

        $this->actingAs($user)
            ->delete('/api/v2/admin/project-reports/' . $report->uuid)
            ->assertStatus(403);
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admins_can_soft_delete_project_reports(string $permission, string $fmKey)
    {
        //        Artisan::call('v2migraton:roles');

        $user = User::factory()->admin()->create();
        $user->givePermissionTo($permission);

        $project = Project::factory()->{$fmKey}()->create();
        $report = ProjectReport::factory()->{$fmKey}()->for($project)->create();

        $uri = '/api/v2/admin/project-reports/' . $report->uuid;

        $this->assertFalse($report->trashed());

        $this->actingAs($user)
            ->delete($uri)
            ->assertSuccessful();

        $report->refresh();

        $this->assertTrue($report->trashed());
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
