<?php

namespace Tests\V2\Projects;

use App\Mail\V2ProjectInviteReceived;
use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateProjectInviteControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        //        Artisan::call('v2migration:roles --fresh');
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_creates_project_invitations_for_existing_users(string $permission, string $fmKey, bool $useUserEmail)
    {
        DB::table('v2_project_invites')->truncate();

        $this->assertDatabaseCount('v2_project_invites', 0);

        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo($permission);

        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->{$fmKey}()->create(['organisation_id' => $organisation->id]);

        $email_address = $useUserEmail ? $user->email_address : $this->faker->email;

        $payload = [
            'email_address' => $email_address,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/projects/' . $project->uuid . '/invite', $payload)
            ->assertStatus(403);

        foreach ([$admin, $owner] as $allowedUser) {
            $this->actingAs($allowedUser)
                ->postJson('/api/v2/projects/' . $project->uuid . '/invite', $payload)
                ->assertStatus(201)
                ->assertJsonFragment([
                    'project_id' => $project->id,
                    'email_address' => $payload['email_address'],
                    'accepted_at' => null,
                ]);
        }

        $this->assertDatabaseCount('v2_project_invites', 2);

        $invites = $project->invites()->get();

        foreach ($invites as $projectInvite) {
            $this->assertDatabaseHas('v2_project_invites', [
                'id' => $projectInvite->id,
                'uuid' => $projectInvite->uuid,
                'project_id' => $project->id,
                'email_address' => $projectInvite->email_address,
                'token' => $projectInvite->token,
                'accepted_at' => null,
            ]);

            $emailBody = 'You have been sent an invite to join ' . e($project->name) . '.<br><br>' .
                'Click below to accept the invite.<br><br>';

            Mail::assertQueued(
                V2ProjectInviteReceived::class,
                function (V2ProjectInviteReceived $projectInviteReceived) use ($email_address, $emailBody) {
                    return $projectInviteReceived->hasTo($email_address) &&
                        $projectInviteReceived->subject('Project Invite') &&
                        $projectInviteReceived->body = $emailBody &&
                        $projectInviteReceived->cta = 'Accept invite';
                }
            );
        }
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund', true],
            ['framework-terrafund', 'terrafund', false],
            ['framework-ppc', 'ppc', true],
            ['framework-ppc', 'ppc', false],
        ];
    }
}
