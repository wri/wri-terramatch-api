<?php

namespace Tests\V2\ReportingFrameworks;

use App\Helpers\CustomFormHelper;
use App\Models\Framework;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateReportingFrameworkControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles');
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo(['framework-ppc', 'framework-terrafund']);

        $framework = Framework::factory()->create();

        $payload = ['access_code' => 'TESTING-123'];
        $uri = '/api/v2/admin/reporting-frameworks/' . $framework->uuid;

        $this->actingAs($user)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment(['access_code' => 'TESTING-123']);
    }

    public function test_clean_up_action()
    {
        $admin = User::factory()->admin()->create();
        Artisan::call('v2migration:roles');
        $admin->givePermissionTo(['framework-ppc', 'framework-terrafund']);

        $formP1 = CustomFormHelper::generateFakeForm('project', 'ppc');
        $formP2 = CustomFormHelper::generateFakeForm('project', 'ppc');
        $formPR1 = CustomFormHelper::generateFakeForm('project-report', 'ppc');
        $formPR2 = CustomFormHelper::generateFakeForm('project-report', 'ppc');
        CustomFormHelper::generateFakeForm('project', 'terrafund');
        CustomFormHelper::generateFakeForm('project-report', 'pterrafundpc');

        $framework = Framework::factory()->create([
            'name' => 'PPC',
            'slug' => 'ppc',
            'project_form_uuid' => $formP1->uuid,
            'project_report_form_uuid' => $formPR1->uuid,
        ]);

        $payload = [
            'project_form_uuid' => $formP2->uuid,
        ];


        $this->actingAs($admin)
            ->putJson('/api/v2/admin/reporting-frameworks/' . $framework->uuid, $payload)
            ->assertStatus(200);

        $this->assertEquals(
            1,
            Form::where('model', Project::class)
                ->where('framework_key', 'ppc')
                ->count()
        );

        $this->assertEquals(
            1,
            Form::where('model', ProjectReport::class)
                ->where('framework_key', 'ppc')
                ->count()
        );

        $this->assertEquals(
            1,
            Form::where('model', Project::class)
                ->where('framework_key', 'terrafund')
                ->count()
        );
    }
}
