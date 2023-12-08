<?php

namespace Tests\V2\NurseryReports;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubmitNurseryReportControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        //        Artisan::call('v2migration:roles --fresh');
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $nursery = Nursery::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $report = NurseryReport::factory()->create([
            'nursery_id' => $nursery->id,
            'framework_key' => 'ppc',
        ]);

        CustomFormHelper::generateFakeForm('nursery-report', 'ppc');

        $uri = '/api/v2/forms/nursery-reports/' . $report->uuid . '/submit';

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful();
    }
}
