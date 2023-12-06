<?php

namespace Tests\V2\NurseryReports;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CreateNurseryReportControllerWithFormTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        //        Artisan::call('v2migration:roles --fresh');
        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

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

        $form = CustomFormHelper::generateFakeForm('nursery-report', 'ppc');

        $payload = [
            'parent_entity' => 'nursery',
            'parent_uuid' => $nursery->uuid,
        ];

        $uri = '/api/v2/forms/nursery-reports';

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertSuccessful();
    }
}
