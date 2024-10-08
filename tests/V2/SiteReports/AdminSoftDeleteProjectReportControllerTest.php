<?php

namespace Tests\V2\SiteReports;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
    public function test_admins_can_soft_delete_project_reports(string $adminType, string $fmKey)
    {
        $user = User::factory()->{$adminType}()->create();
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
            ['terrafundAdmin', 'terrafund'],
            ['ppcAdmin', 'ppc'],
        ];
    }
}
