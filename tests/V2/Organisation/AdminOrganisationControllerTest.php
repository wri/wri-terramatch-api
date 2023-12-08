<?php

namespace Tests\V2\Organisation;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminOrganisationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_index_action(): void
    {
        //        $count = Organisation::count();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        Organisation::factory()->count(8)->create();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations')
            ->assertStatus(200);
        //            ->assertJsonCount(8 + $count, 'data');

        $this->actingAs($user)
            ->getJson('/api/v2/admin/organisations')
            ->assertStatus(403);
    }

    public function test_show_action(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $organisations = Organisation::factory()->count(6)->create();
        $organisation = $organisations[3];

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations/'. $organisation->uuid)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $organisation->uuid,
                'status' => $organisation->status,
                'type' => $organisation->type,
            ]);

        $this->actingAs($user)
            ->getJson('/api/v2/admin/organisations/'. $organisation->uuid)
            ->assertStatus(403);
    }

    public function test_update_action(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $organisations = Organisation::factory()->count(4)->create();
        $organisation = $organisations[2];

        $payload = [
            'name' => 'Organisation Testing',
            'phone' => '01234 567890',
            'web_url' => 'www.testing.com',
        ];

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/organisations/'. $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $organisation->uuid,
                'name' => 'Organisation Testing',
                'type' => $organisation->type,
                'phone' => '01234 567890',
            ]);

        $this->actingAs($user)
            ->putJson('/api/v2/admin/organisations/'. $organisation->uuid, $payload)
            ->assertStatus(403);
    }

    public function test_delete_action(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $organisations = Organisation::factory()->count(4)->create();
        $organisation = $organisations[2];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/organisations/'. $organisation->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/admin/organisations/'. $organisation->uuid)
            ->assertSuccessful();
    }

    public function test_search_sort_and_filtering(): void
    {
        $admin = User::factory()->admin()->create();
        Organisation::factory()->count(2)->create(['name' => 'xxxx', 'status' => Organisation::STATUS_APPROVED, 'type' => Organisation::TYPE_FOR_PROFIT]);
        Organisation::factory()->count(9)->create(['name' => 'xxxx', 'status' => Organisation::STATUS_APPROVED, 'type' => Organisation::TYPE_NON_PROFIT]);
        Organisation::factory()->count(4)->create(['name' => 'xxxx', 'status' => Organisation::STATUS_APPROVED, 'type' => Organisation::TYPE_GOVERNMENT]);
        Organisation::factory()->count(7)->create(['name' => 'xxxx', 'status' => Organisation::STATUS_PENDING, 'type' => Organisation::TYPE_NON_PROFIT]);
        Organisation::factory()->count(8)->create(['name' => 'xxxx', 'status' => Organisation::STATUS_DRAFT, 'type' => Organisation::TYPE_NON_PROFIT]);
        Organisation::factory()->count(2)->create(['name' => 'xxxx', 'status' => Organisation::STATUS_DRAFT, 'type' => Organisation::TYPE_GOVERNMENT]);
        Organisation::factory()->create(['name' => 'fawks123', 'status' => Organisation::STATUS_APPROVED, 'type' => Organisation::TYPE_FOR_PROFIT]);
        Organisation::factory()->create(['name' => 'guyfawks123', 'status' => Organisation::STATUS_PENDING, 'type' => Organisation::TYPE_GOVERNMENT]);
        Organisation::factory()->create(['name' => 'Remus23', 'status' => Organisation::STATUS_APPROVED, 'type' => Organisation::TYPE_FOR_PROFIT]);
        Organisation::factory()->create(['name' => 'Miles23', 'status' => Organisation::STATUS_APPROVED, 'type' => Organisation::TYPE_GOVERNMENT]);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations?search=fawks1')
            ->assertSuccessful();
        //            ->assertJsonCount(2, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations?filter[status]=' . Organisation::STATUS_APPROVED . '&filter[type]=' . Organisation::TYPE_FOR_PROFIT . '&sort=-name')
            ->assertSuccessful();
        //            ->assertJsonCount(4, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations?search=23&filter[status]=' . Organisation::STATUS_APPROVED . ',' . Organisation::STATUS_PENDING . '&filter[type]=' . Organisation::TYPE_GOVERNMENT . '&sort=-name')
            ->assertSuccessful();
        //            ->assertJsonCount(2, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations?&sort=test')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations?&sort=-name')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations?&sort=fin_budget_1year')
            ->assertSuccessful();
    }

    public function test_tagging_action(): void
    {
        $admin = User::factory()->admin()->create();
        $organisations = Organisation::factory()->count(6)->create();
        $organisation = $organisations[3];

        $payload = [
            'tags' => ['Red', 'Piano', 'Furry Cat'],
        ];

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/organisations/'. $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'tags' => [
                    'red' => 'Red',
                    'piano' => 'Piano',
                    'furry-cat' => 'Furry Cat',
                ],
            ]);
    }
}
