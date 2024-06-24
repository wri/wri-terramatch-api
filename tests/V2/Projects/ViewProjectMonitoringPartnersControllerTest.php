<?php

namespace Tests\V2\Projects;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ViewProjectMonitoringPartnersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_it_returns_project_partners()
    {
        Artisan::call('v2migration:roles');
        $organisation = Organisation::factory()->create();

        $user = User::factory()->create(['organisation_id' => $organisation->id]);
        $user->givePermissionTo('manage-own');

        $otherUser = User::factory()->create(['organisation_id' => $organisation->id]);
        $otherUser->givePermissionTo('manage-own');

        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $monitoredProject = Project::factory()->create([
            'framework_key' => 'terrafund',
        ]);

        $user->projects()->attach($monitoredProject);

        ProjectInvite::factory()->create([
            'project_id' => $monitoredProject->id,
            'email_address' => $user->email_address,
        ]);

        ProjectInvite::factory()->create([
            'project_id' => $monitoredProject->id,
        ]);

        $uri = '/api/v2/projects/' . $monitoredProject->uuid . '/partners';

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'uuid' => $user->uuid,
            ])
            ->assertJsonFragment([
                'uuid' => $user->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $otherUser->uuid,
            ]);
    }
}
