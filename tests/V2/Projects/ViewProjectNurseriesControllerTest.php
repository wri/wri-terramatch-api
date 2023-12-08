<?php

namespace Tests\V2\Projects;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ViewProjectNurseriesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $ppcAdmin;

    private $user;

    private $owner;

    private $project;

    private $nurseries;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('v2migration:roles --fresh');
        $this->ppcAdmin = User::factory()->admin()->create();
        $this->ppcAdmin->givePermissionTo('framework-ppc');

        $this->user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $this->owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $this->owner->givePermissionTo('manage-own');

        $this->project = Project::factory()
            ->has(
                Nursery::factory()
                    ->count(10)
                    ->state(new Sequence(
                        ['name' => 'nursery started', 'status' => 'started'],
                        ['name' => 'nursery awaiting approval', 'status' => 'awaiting-approval'],
                        ['name' => 'nursery approved', 'status' => 'approved'],
                        ['name' => 'nursery due', 'status' => 'due'],
                        ['name' => 'nursery needs more info', 'status' => 'needs-more-information'],
                    ))
                    ->terrafund()
                    ->has(
                        NurseryReport::factory()
                            ->terrafund(),
                        'reports'
                    )
            )
            ->terrafund()
            ->create([
                'organisation_id' => $organisation->id,
            ]);

        NurseryReport::factory()->count(5)->create();
        $this->nurseries = $this->project->nurseries()->get();
    }

    public function test_invoke_action()
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries')
            ->assertStatus(403);

        $this->actingAs($this->ppcAdmin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries')
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries')
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $this->nurseries[0]->uuid,
                'name' => $this->nurseries[0]->name,
                'framework_key' => $this->nurseries[0]->framework_key,
                'status' => $this->nurseries[0]->status,
                'readable_status' => $this->nurseries[0]->readable_status,
                'start_date' => $this->nurseries[0]->start_date,
                'created_at' => $this->nurseries[0]->created_at,
            ]);
    }

    public function test_filtering_by_status_works()
    {
        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?filter[status]=started')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'started')
            ->assertJsonPath('data.1.status', 'started');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?filter[status]=awaiting-approval')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'awaiting-approval')
            ->assertJsonPath('data.1.status', 'awaiting-approval');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?filter[status]=approved')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.1.status', 'approved');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?filter[status]=due')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'due')
            ->assertJsonPath('data.1.status', 'due');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?filter[status]=needs-more-information')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'needs-more-information')
            ->assertJsonPath('data.1.status', 'needs-more-information');
    }

    public function test_searching_by_name_works()
    {
        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?search=nursery st')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'started')
            ->assertJsonPath('data.1.status', 'started');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?search=nursery awaiting')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'awaiting-approval')
            ->assertJsonPath('data.1.status', 'awaiting-approval');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?search=nursery appr')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.1.status', 'approved');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?search=nursery due')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'due')
            ->assertJsonPath('data.1.status', 'due');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?search=nursery needs')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'needs-more-information')
            ->assertJsonPath('data.1.status', 'needs-more-information');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?search=site')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function test_that_nurseries_are_paginated()
    {
        Nursery::factory()->count(10)
            ->create([
                'project_id' => $this->project->id,
            ]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?page=1')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?page=2')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');
    }

    public function test_that_nurseries_are_sortable()
    {
        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?sort=-name')
            ->assertSuccessful()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.name', 'nursery started')
            ->assertJsonPath('data.2.name', 'nursery needs more info')
            ->assertJsonPath('data.4.name', 'nursery due')
            ->assertJsonPath('data.6.name', 'nursery awaiting approval')
            ->assertJsonPath('data.8.name', 'nursery approved');

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?sort=-status')
            ->assertSuccessful()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.status', 'started')
            ->assertJsonPath('data.2.status', 'needs-more-information')
            ->assertJsonPath('data.4.status', 'due')
            ->assertJsonPath('data.6.status', 'awaiting-approval')
            ->assertJsonPath('data.8.status', 'approved');

        $newNursery = Nursery::factory()->create([
            'project_id' => $this->project->id,
            'created_at' => now()->addDay(),
        ]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/nurseries?sort=-created_at')
            ->assertSuccessful()
            ->assertJsonCount(11, 'data')
            ->assertJsonPath('data.0.uuid', $newNursery->uuid);
    }
}
