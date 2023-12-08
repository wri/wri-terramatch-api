<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class ProgrammeControllerTest extends LegacyTestCase
{
    public function testReadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/programme/1/overview', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Example programme',
            'workdays_paid' => 62,
            'workdays_volunteer' => 96,
            'total_workdays' => 158,
        ])
        ->assertJsonPath('data.submissions.0.id', 1);
    }

    public function testReadActionAllowsAdminsToRead(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $this->getJson('/api/programme/1/overview', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Example programme',
            'workdays_paid' => 62,
            'workdays_volunteer' => 96,
        ])
        ->assertJsonPath('data.submissions.0.id', 1);
    }

    public function testReadAllAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $this->getJson('/api/programmes', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Example programme',
        ]);
    }

    public function testReadAllPersonalAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/programmes/personal', $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 1,
            'name' => 'Example programme',
        ]);
    }

    public function testReadAllActionRequiresBeingAnAdmin(): void
    {
        $headers = $this->getHeaders('joe@example.com', 'Password123');

        $this->getJson('/api/programmes', $headers)
        ->assertStatus(403);
    }

    public function testReadActionRequiresBelongingToProgramme(): void
    {
        $headers = $this->getHeaders('sue@example.com', 'Password123');

        $this->getJson('/api/programme/1/overview', $headers)
        ->assertStatus(403);
    }

    public function testCreateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->postJson('/api/programme', [
            'name' => 'Steve\'s new programme',
            'continent' => 'europe',
            'country' => 'SE',
            'end_date' => '2031-10-06',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => "Steve's new programme",
            'continent' => 'europe',
            'country' => 'SE',
            'end_date' => '2031-10-06',
            'framework_id' => 1,
            'organisation_id' => 1,
        ]);

        $this->assertDatabaseHas('programme_user', [
            'user_id' => 3,
            'programme_id' => $response->json('data.id'),
        ]);
    }

    public function testUpdateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->patchJson('/api/programme/1', [
            'name' => 'Steve\'s updated programme name',
        ], $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Steve\'s updated programme name',
        ]);
    }

    public function testUpdateAdditionalTreeSpeciesAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);


        $data = [
            'name' => 'Steve\'s updated programme name',
            'additional_tree_species' => $uploadResponse->json('data.id'),
        ];

        $response = $this->patchJson('/api/programme/1', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Steve\'s updated programme name',
            ]);
    }

    public function testCreateActionRequiresPPCUser(): void
    {
        $headers = $this->getHeaders('andrew@example.com', 'Password123');

        $response = $this->postJson('/api/programme', [
            'name' => 'Andrew\'s new programme',
            'continent' => 'europe',
            'country' => 'SE',
            'end_date' => '2031-10-06',
        ], $headers)
        ->assertStatus(403);
    }

    public function testCreateActionNameIsRequired(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/programme', [
            'continent' => 'europe',
            'country' => 'SE',
            'end_date' => '2031-10-06',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionCountryIsRequired(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/programme', [
            'name' => "Steve's new programme",
            'continent' => 'europe',
            'end_date' => '2031-10-06',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionContinentIsRequired(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/programme', [
            'name' => "Steve's new programme",
            'country' => 'SE',
            'end_date' => '2031-10-06',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionEndDateIsRequired(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/programme', [
            'name' => "Steve's new programme",
            'continent' => 'europe',
            'country' => 'SE',
        ], $headers)
        ->assertStatus(422);
    }

    public function testAddBoundaryToProgrammeAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [
            'programme_id' => 1,
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
        ];
        $response = $this->postJson('/api/programme/boundary', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }
}
