<?php

namespace Tests\V2\ProjectReports;

use App\Models\Framework;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminIndexProjectReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();
        Framework::factory()->create(['slug' => 'terrafund']);
        Framework::factory()->create(['slug' => 'ppc']);
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
        $tfAdmin = User::factory()->terrafundAdmin()->create();

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
