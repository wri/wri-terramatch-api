<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class OrganisationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testIndexAction(): void
    {
        $organisations = Organisation::factory()->count(8)->create();
        $organisation = $organisations[4];
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->organisations()->sync([$organisations[2]->id,$organisations[6]->id]);

        $this->actingAs($user)
            ->getJson('/api/v2/organisations')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testShowAction(): void
    {
        $organisations = Organisation::factory()->count(8)->create();
        $organisation = $organisations[4];
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->organisations()->sync([$organisations[2]->id,$organisations[6]->id]);

        TreeSpecies::factory()->count(3)->create(['speciesable_type' => Organisation::class, 'speciesable_id' => $organisation->id]);
        $organisation->fresh();

        $this->actingAs($user)
            ->getJson('/api/v2/organisations/'. $organisation->uuid)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $organisation->uuid,
                'status' => $organisation->status,
                'type' => $organisation->type,
            ]);

        $this->actingAs($user)
            ->getJson('/api/v2/organisations/'. $organisations[3]->uuid)
            ->assertStatus(403);
    }

    public function testCreateAction(): void
    {
        $user = User::factory()->create(['organisation_id' => null]);

        $payload =
            Organisation::factory()->make([
                'name' => 'Test Alpha Organisation',
                'type' => 'for-profit-enterprise',
            ])->toArray();

        $payload['shapefiles'] = [
            '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
            '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
        ];

        $this->postJson('/api/v2/organisations', $payload)
            ->assertStatus(403);

        $response = $this->actingAs($user)
            ->postJson('/api/v2/organisations', $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'type' => 'for-profit-enterprise',
                'name' => 'Test Alpha Organisation',
            ]);
        $uuid = json_decode($response->getContent())->data->uuid;
        $organisation = Organisation::isUuid($uuid)->first();

        $this->assertDatabaseCount('shapefiles', 2);

        $user->fresh();
        $this->assertEquals($user->organisation_id, $organisation->id);

        $this->actingAs($user)
            ->postJson('/api/v2/organisations', $payload)
            ->assertStatus(406);
    }

    public function testUpdateAction(): void
    {
        $organisations = Organisation::factory()->count(4)->create();
        $organisation = $organisations[2];
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $payload = [
            'phone' => $this->faker->phoneNumber(),
            'hq_street_1' => $this->faker->streetAddress(),
            'hq_street_2' => $this->faker->streetAddress(),
            'hq_city' => $this->faker->city(),
            'hq_state' => $this->faker->state(),
            'hq_zipcode' => $this->faker->postcode(),
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/organisations/'. $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'phone' => $payload['phone'],
                'hq_street_1' => $payload['hq_street_1'],
                'hq_street_2' => $payload['hq_street_2'],
                'hq_city' => $payload['hq_city'],
                'hq_state' => $payload['hq_state'],
                'hq_zipcode' => $payload['hq_zipcode'],
            ]);
    }
}
