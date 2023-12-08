<?php

namespace Tests\V2\UpdateRequests;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\UpdateRequests\UpdateRequest;
//use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSoftDeleteUpdateRequestControllerTest extends TestCase
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

        $updateRequest = UpdateRequest::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'updaterequestable_type' => Site::class,
            'updaterequestable_id' => $site->id,
        ]);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $random = User::factory()->create();
        $random->givePermissionTo('manage-own');

        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

        $uri = '/api/v2/admin/update-requests/' . $updateRequest->uuid;

        $this->actingAs($random)
            ->deleteJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->deleteJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson($uri)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->deleteJson($uri)
            ->assertSuccessful();
    }
}
