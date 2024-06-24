<?php

namespace Tests\V2\Projects;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProjectInviteAcceptControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        Mail::fake();
        Artisan::call('v2migration:roles');

        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $invitedUser = User::factory()->create();
        $otherUser = User::factory()->create();

        $invite = ProjectInvite::factory()->create([
            'project_id' => $project->id,
            'email_address' => $invitedUser->email_address,
        ]);

        $payload = ['token' => $invite->token];
        $uri = '/api/v2/projects/invite/accept';

        $this->actingAs($otherUser)
            ->postJson($uri, $payload)
            ->assertStatus(404);

        $this->actingAs($invitedUser)
            ->postJson($uri, $payload)
            ->assertSuccessful();

        $invitedUser->fresh();

        $this->assertTrue($invitedUser->projects->contains($project->id));
    }
}
