<?php

namespace Tests\V2\Organisation;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class OrganisationApprovedUsersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_listing_action(): void
    {
        $randomer = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $approvedUsers = User::factory()->count(3)->create();
        $requestedUser = User::factory()->create();

        $organisation = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $organisation->partners()->attach($approvedUsers->pluck('id')->toArray(), ['status' => 'approved']);
        $organisation->partners()->attach($requestedUser, ['status' => 'requested']);

        $this->actingAs($randomer)
            ->getJson('/api/v2/organisations/approved-users/' . $organisation->uuid)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson('/api/v2/organisations/approved-users/' . $this->faker->uuid())
            ->assertStatus(404);

        $this->actingAs($owner)
            ->getJson('/api/v2/organisations/approved-users/' . $organisation->uuid)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/organisations/approved-users/' . $organisation->uuid)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');
    }
}
