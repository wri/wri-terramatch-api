<?php

namespace Tests\V2\ProjectReports;

use App\Models\User;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminIndexProjectReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        //         Artisan::call('v2migration:roles');
        $tfAdmin = User::factory()->admin()->create();
        $ppcAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');
        $ppcAdmin->givePermissionTo('framework-ppc');
        $user = User::factory()->create();

        ProjectReport::query()->delete();
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();
        ProjectReport::factory()->count(3)->create(['framework_key' => 'terrafund']);
        ProjectReport::factory()->count(2)->create(['framework_key' => 'terrafund', 'project_id' => $project1->id]);
        ProjectReport::factory()->count(5)->create(['framework_key' => 'ppc']);
        ProjectReport::factory()->count(2)->create(['framework_key' => 'ppc', 'project_id' => $project2->id]);

        // This will create a soft deleted project that should not appear on the results
        (ProjectReport::factory()->create(['framework_key' => 'ppc']))->delete();
        (ProjectReport::factory()->create(['framework_key' => 'terrafund']))->delete();

        $uri = '/api/v2/admin/project-reports';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(7, 'data');

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');
    }

    public function test_searching_on_project_name()
    {
        // Artisan::call('v2migration:roles');
        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        ProjectReport::query()->delete();
        $project1 = Project::factory()->terrafund()->create();
        $project2 = Project::factory()->terrafund()->create();
        ProjectReport::factory()->terrafund()->count(3)->create(['project_id' => $project1]);
        ProjectReport::factory()->terrafund()->count(3)->create(['project_id' => $project2]);


        $uri = '/api/v2/admin/project-reports';

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(6, 'data');

        $this->actingAs($tfAdmin)
            ->getJson($uri . '?search=' . $project1->name)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }
}
