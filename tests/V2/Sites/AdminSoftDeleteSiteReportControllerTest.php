<?php

namespace Sites;

use App\Models\User;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminSoftDeleteSiteReportControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_users_cant_soft_delete_site_reports()
    {
        $user = User::factory()->create();

        $site = Site::factory()->ppc()->create();

        $report = SiteReport::factory()->for($site)->create();

        $this->actingAs($user)
            ->delete('/api/v2/admin/site-reports/' . $report->uuid)
            ->assertStatus(403);
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admins_can_soft_delete_site_reports(string $permission, string $fmKey)
    {
        //        Artisan::call('v2migration:roles');

        $user = User::factory()->admin()->create();
        $user->givePermissionTo($permission);

        $site = Site::factory()->{$fmKey}()->create();
        $report = SiteReport::factory()->{$fmKey}()->for($site)->create();

        $uri = '/api/v2/admin/site-reports/' . $report->uuid;

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
