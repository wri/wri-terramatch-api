<?php

namespace Tests\V2\Audits;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminIndexAuditsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admin_can_fetch_all_audit_logs_for_a_given_entity(string $permission, string $fmKey)
    {
        Artisan::call('v2migration:roles');

        $user = User::factory()->admin()->create();
        $user->givePermissionTo($permission);

        $testCases = [
            'project' => Project::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'site' => Site::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'nursery' => Nursery::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'project-report' => ProjectReport::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'site-report' => SiteReport::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'nursery-report' => NurseryReport::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
        ];

        foreach ($testCases as $entity => $model) {
            $model->status = 'needs-more-information';
            $model->feedback = 'testing feedback';
            $model->save();

            $model->status = 'approved';
            $model->save();

            $this->actingAs($user)
                ->getJson('/api/v2/admin/audits/' . $entity . '/' . $model->uuid)
                ->assertJsonPath('data.0.event', 'updated')
                ->assertJsonPath('data.0.new_values.status', 'approved')
                ->assertJsonPath('data.0.old_values.status', 'needs-more-information')
                ->assertJsonPath('data.1.event', 'updated')
                ->assertJsonPath('data.1.old_values.status', 'started')
                ->assertJsonPath('data.1.new_values.status', 'needs-more-information')
                ->assertJsonPath('data.1.new_values.feedback', 'testing feedback')
                ->assertJsonPath('data.2.event', 'created')
                ->assertJsonPath('data.2.new_values.status', 'started')
                ->assertJsonPath('data.2.old_values', [])
                ->assertStatus(200);
        }
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
