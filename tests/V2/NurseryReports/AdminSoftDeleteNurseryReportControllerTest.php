<?php

namespace Tests\V2\NurseryReports;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSoftDeleteNurseryReportControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_users_cant_soft_delete_nursery_reports()
    {
        $user = User::factory()->create();

        $nursery = Nursery::factory()->ppc()->create();

        $report = NurseryReport::factory()->for($nursery)->create();

        $this->actingAs($user)
            ->delete('/api/v2/admin/nursery-reports/' . $report->uuid)
            ->assertStatus(403);
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admins_can_soft_delete_nursery_reports(string $adminType, string $fmKey)
    {
        $user = User::factory()->{$adminType}()->create();
        $nursery = Nursery::factory()->{$fmKey}()->create();
        $report = NurseryReport::factory()->{$fmKey}()->for($nursery)->create();

        $uri = '/api/v2/admin/nursery-reports/' . $report->uuid;

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
