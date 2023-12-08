<?php

namespace Tests\V2\Organisation;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class OrganisationRetractMyDraftControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_valid_invoke_action(): void
    {
        $organisation = Organisation::factory()->create(['status' => Organisation::STATUS_DRAFT]);
        $owner = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/organisations/retract-my-draft')
            ->assertSuccessful();
    }

    public function test_invalid_invoke_action(): void
    {
        $randomUser = User::factory()->create();
        $organisation = Organisation::factory()->create(['status' => Organisation::STATUS_PENDING]);
        $owner = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $this->actingAs($randomUser)
            ->deleteJson('/api/v2/organisations/retract-my-draft')
            ->assertStatus(406);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/organisations/retract-my-draft')
            ->assertStatus(406);
    }
}
