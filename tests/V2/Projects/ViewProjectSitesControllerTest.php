<?php

namespace Tests\V2\Projects;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ViewProjectSitesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $ppcAdmin;

    private $user;

    private $owner;

    private $project;

    private $sites;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('v2migration:roles');

        $this->ppcAdmin = User::factory()->admin()->create();
        $this->ppcAdmin->givePermissionTo('framework-ppc');

        $this->user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $this->owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $this->owner->givePermissionTo('manage-own');

        $this->project = Project::factory()
            ->has(
                Site::factory()
                    ->count(10)
                    ->state(new Sequence(
                        ['name' => 'site started', 'status' => 'started'],
                        ['name' => 'site awaiting approval', 'status' => 'awaiting-approval'],
                        ['name' => 'site approved', 'status' => 'approved'],
                        ['name' => 'site due', 'status' => 'due'],
                        ['name' => 'site needs more info', 'status' => 'needs-more-information'],
                    ))
                    ->terrafund()
                    ->has(
                        SiteReport::factory()
                            ->hasTreeSpecies(2)
                            ->terrafund(),
                        'reports'
                    )
            )
            ->terrafund()
            ->create([
                'organisation_id' => $organisation->id,
            ]);
        Site::factory()->count(5)->create();
        $this->sites = $this->project->sites()->get();
    }

    public function test_invoke_action()
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites')
            ->assertStatus(403);

        $this->actingAs($this->ppcAdmin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites')
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites')
            ->assertSuccessful()
            ->assertJsonCount(10, 'data')
            ->assertJsonFragment([
                'uuid' => $this->sites[0]->uuid,
                'name' => $this->sites[0]->name,
                'framework_key' => $this->sites[0]->framework_key,
                'description' => $this->sites[0]->description,
                'status' => $this->sites[0]->status,
                'readable_status' => $this->sites[0]->readable_status,
                'number_of_trees_planted' => $this->sites[0]->trees_planted_count,
                'start_date' => $this->sites[0]->start_date,
                'created_at' => $this->sites[0]->created_at,
            ]);
    }

    public function test_filtering_by_status_works()
    {
        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?filter[status]=started')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'started')
            ->assertJsonPath('data.1.status', 'started');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?filter[status]=awaiting-approval')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'awaiting-approval')
            ->assertJsonPath('data.1.status', 'awaiting-approval');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?filter[status]=approved')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.1.status', 'approved');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?filter[status]=due')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'due')
            ->assertJsonPath('data.1.status', 'due');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?filter[status]=needs-more-information')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'needs-more-information')
            ->assertJsonPath('data.1.status', 'needs-more-information');
    }

    public function test_searching_by_name_works()
    {
        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?search=site st')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'started')
            ->assertJsonPath('data.1.status', 'started');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?search=site awaiting')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'awaiting-approval')
            ->assertJsonPath('data.1.status', 'awaiting-approval');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?search=site appr')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.1.status', 'approved');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?search=site due')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'due')
            ->assertJsonPath('data.1.status', 'due');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?search=site needs')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'needs-more-information')
            ->assertJsonPath('data.1.status', 'needs-more-information');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?search=nursery')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function test_that_sites_are_paginated()
    {
        Site::factory()->count(10)
            ->create([
                'project_id' => $this->project->id,
            ]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?page=1')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?page=2')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');
    }

    public function test_that_sites_are_sortable()
    {
        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?sort=-name')
            ->assertSuccessful()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.name', 'site started')
            ->assertJsonPath('data.2.name', 'site needs more info')
            ->assertJsonPath('data.4.name', 'site due')
            ->assertJsonPath('data.6.name', 'site awaiting approval')
            ->assertJsonPath('data.8.name', 'site approved');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?sort=-status')
            ->assertSuccessful()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.status', 'started')
            ->assertJsonPath('data.2.status', 'needs-more-information')
            ->assertJsonPath('data.4.status', 'due')
            ->assertJsonPath('data.6.status', 'awaiting-approval')
            ->assertJsonPath('data.8.status', 'approved');

        $newSite = Site::factory()->create([
                'project_id' => $this->project->id,
                'created_at' => now()->addDay(),
            ]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?sort=-created_at')
            ->assertSuccessful()
            ->assertJsonCount(11, 'data')
            ->assertJsonPath('data.0.uuid', $newSite->uuid);

        Site::factory()
            ->terrafund()
            ->has(
                SiteReport::factory()
                    ->hasTreeSpecies(1, ['amount' => 0])
                    ->terrafund(),
                'reports'
            )->create(['project_id' => $this->project->id,]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/sites?sort=-number_of_trees_planted')
            ->assertJsonCount(12, 'data')
            ->assertSuccessful()
            ->assertJsonPath('data.11.number_of_trees_planted', 0);
    }
}
