<?php

namespace Tests\V2\Projects;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewMyProjectsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        $organisation = Organisation::factory()->create();
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        $organisationProject = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'terrafund',
        ]);

        $organisationMonitoredProject = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'terrafund',
        ]);

        $monitoredProject = Project::factory()->create([
            'framework_key' => 'terrafund',
        ]);

        $unmonitoredProject = Project::factory()->create([
            'framework_key' => 'terrafund',
        ]);

        $user->projects()->attach($monitoredProject);
        $user->projects()->attach($organisationMonitoredProject);

        $uri = '/api/v2/my/projects/';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing([
                'uuid' => $organisationProject->uuid,
            ])
            ->assertJsonFragment([
                'uuid' => $monitoredProject->uuid,
            ])
            ->assertJsonFragment([
                'uuid' => $organisationMonitoredProject->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $unmonitoredProject->uuid,
            ]);
    }
}
