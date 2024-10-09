<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class OrganisationSubmitControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        $organisation = Organisation::factory(['status' => Organisation::STATUS_DRAFT])->create();
        $user = User::factory()->create(['organisation_id' => $organisation->id, 'locale' => 'en-US']);
        $this->actingAs($user);

        $this->actingAs($user)
            ->putJson('/api/v2/organisations/submit/' . $organisation->uuid, [])
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $organisation->uuid,
                'status' => Organisation::STATUS_PENDING,
            ]);
    }

    public function test_validation(): void
    {
        $organisation = Organisation::factory(['status' => Organisation::STATUS_DRAFT, 'name' => null])->create();
        $user = User::factory()->create(['organisation_id' => $organisation->id, 'locale' => 'en-US']);
        $this->actingAs($user);

        $this->actingAs($user)
            ->putJson('/api/v2/organisations/submit/' . $organisation->uuid, [])
            ->assertStatus(422);
    }
}
