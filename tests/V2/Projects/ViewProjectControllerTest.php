<?php

namespace Tests\V2\Projects;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ViewProjectControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        //        Artisan::call('v2migration:roles --fresh');
        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

        $user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'terrafund',
        ]);

        $uri = '/api/v2/projects/' . $project->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();
    }

    public function test_it_does_not_return_soft_deleted_projects()
    {
        //        Artisan::call('v2migration:roles --fresh');
        $organisation = Organisation::factory()->create();
        $user = User::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $project = Project::factory()->create(['organisation_id' => $organisation->id]);
        CustomFormHelper::generateFakeForm('project', 'ppc');

        $project->delete();

        $user->givePermissionTo('manage-own');
        $owner->givePermissionTo('manage-own');

        $uri = '/api/v2/projects/' . $project->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(404);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(404);
    }
}
