<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class OrganisationListRequestedUsersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testInvokeAction(): void
    {
        $admin = User::factory()->admin()->create();
        $approvedUsers = User::factory()->count(3)->create();
        $requestedUsers = User::factory()->count(2)->create();

        $organisation = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->create();
        $organisation->partners()->attach($approvedUsers->pluck('id')->toArray(), ['status' => 'approved']);
        $organisation->partners()->attach($requestedUsers->pluck('id')->toArray(), ['status' => 'requested']);

        $this->actingAs($approvedUsers[1])
            ->getJson('/api/v2/organisations/user-requests/' . $organisation->uuid)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/organisations/user-requests/' . $organisation->uuid)
            ->assertSuccessful();

        $this->actingAs($requestedUsers[1])
            ->getJson('/api/v2/organisations/user-requests/' . $organisation->uuid)
            ->assertStatus(403);
    }
}
