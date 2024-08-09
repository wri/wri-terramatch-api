<?php

namespace Tests\V2\Users;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use App\Models\V2\User as V2User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_index_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        V2User::factory()->count(15)->create();
        V2User::factory()->count(5)->create(['email_address_verified_at' => null]);
        $organisation = Organisation::factory()->create();
        $userWithOrg = V2User::factory()->create(['first_name' => 'Frederick12', 'last_name' => 'Verdie20', 'organisation_id' => $organisation->id]);
        V2User::factory()->create(['first_name' => 'Fred19', 'last_name' => 'Mally35']);
        V2User::factory()->create(['first_name' => 'Sally39', 'last_name' => 'Fawkins15']);
        V2User::factory()->create(['first_name' => 'McNally3', 'last_name' => 'Derick15']);

        $this->actingAs($user)
            ->getJson('/api/v2/admin/users')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data'); // response is paginated by 15

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?sort=email_address_verified_at')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data') // response is paginated by 15
            ->assertJsonPath('data.0.email_address_verified_at', null);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?search=derick1')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?search=ally3')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?per_page=5')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?search=ally3&sort=-last_name')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?search=ally3&sort=organisation_name')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?filter[verified]=false&sort=-first_name')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?filter[organisation_id]=' . $userWithOrg->organisation_id)
            ->assertJsonCount(1, 'data')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?filter[organisation_uuid]=' . $organisation->uuid)
            ->assertJsonCount(1, 'data')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?per_page=10&page=1&sort=last_logged_in_at')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?per_page=10&page=1&sort=email_address')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users?per_page=10&page=1&sort=users.created_at')
            ->assertSuccessful();
    }

    public function test_show_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $users = V2User::factory()->count(3)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/users/' . $users[2]->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users/' . $users[2]->uuid)
            ->assertSuccessful();
    }

    public function test_create_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $payload = V2User::factory()->make()->toArray();

        $this->actingAs($user)
            ->postJson('/api/v2/admin/users', $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson('/api/v2/admin/users', $payload)
            ->assertSuccessful();
    }

    public function test_an_user_can_be_updated()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $uri = '/api/v2/admin/users/' . $user->uuid;

        $payload = [
            'first_name' => 'test',
            'last_name' => 'user',
        ];

        $this->actingAs($user)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'first_name' => 'test',
                'last_name' => 'user',
            ]);
    }

    /**
     * @dataProvider rolesDataProvider
     */
    public function test_an_user_can_have_been_assigned_with_a_role(string $primaryRole)
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $uri = '/api/v2/admin/users/' . $user->uuid;

        $payload = [
            'primary_role' => $primaryRole,
            'role' => '', // this is sent like this FED SIDE
        ];

        $this->actingAs($admin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'role' => $primaryRole,
            ]);
    }

    public static function rolesDataProvider()
    {
        return [
            ['admin-super'],
            ['admin-ppc'],
            ['admin-terrafund'],
            ['project-developer'],
        ];
    }

    public function test_update_organisations()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $newPrime = Organisation::factory()->create();
        $newMonitoring = Organisation::factory()->count(2)->create();
        $uri = '/api/v2/admin/users/' . $user->uuid;

        $payload = [
            'organisation' => $newPrime->uuid,
            'monitoring_organisations' => [$newMonitoring[0]->uuid, $newMonitoring[1]->uuid],
        ];

        $this->actingAs($admin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonPath('data.organisation.uuid', $newPrime->uuid)
            ->assertJsonCount(2, 'data.monitoring_organisations');

        $this->actingAs($admin)
            ->putJson($uri, ['name' => 'New Name Ltd.'])
            ->assertSuccessful()
            ->assertJsonPath('data.organisation.uuid', $newPrime->uuid)
            ->assertJsonCount(2, 'data.monitoring_organisations');

        $this->actingAs($admin)
            ->putJson($uri, ['monitoring_organisations' => []])
            ->assertSuccessful()
            ->assertJsonPath('data.organisation.uuid', $newPrime->uuid)
            ->assertJsonCount(0, 'data.monitoring_organisations');
    }

    public function test_update_email(): void
    {
        $user = User::factory()->create();
        $existingUser = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $uri = '/api/v2/admin/users/' . $user->uuid;

        $this->actingAs($admin)
            ->putJson($uri, ['email_address' => $user->email_address])
            ->assertSuccessful();

        $this->actingAs($admin)
            ->putJson($uri, ['email_address' => $existingUser->email_address])
            ->assertStatus(422);

        $this->actingAs($admin)
            ->putJson($uri,  ['email_address' => 'my_new_email@tesingtest.com'])
            ->assertSuccessful();
    }

    public function test_delete_action(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $userToDelete = User::factory()->create();
        $uri = '/api/v2/admin/users/' . $userToDelete->uuid;

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->deleteJson($uri)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertStatus(404);
    }
}
