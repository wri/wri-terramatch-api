<?php

namespace Tests\Legacy\Feature;

use App\Models\Submission;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class SubmissionControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [
            'title' => 'test title',
            'technical_narrative' => 'test tech narrative',
            'public_narrative' => 'test public narrative',
            'created_by' => 'test user',
            'workdays_paid' => 99998,
            'workdays_volunteer' => 99999,
        ];

        $response = $this->postJson('/api/programme/1/submission', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => 'test title',
            'technical_narrative' => 'test tech narrative',
            'public_narrative' => 'test public narrative',
            'created_by' => 'test user',
            'workdays_paid' => 99998,
            'workdays_volunteer' => 99999,
            'total_workdays' => 199997,
        ]);
    }

    public function testUpdateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [
            'title' => 'a new title',
            'technical_narrative' => 'a new test tech narrative',
            'workdays_paid' => 4,
            'workdays_volunteer' => 12,
        ];

        $response = $this->patchJson('/api/programme/submission/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }

    public function testUpdateActionAsAdmin(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'title' => 'admin edit',
            'technical_narrative' => 'admin edit',
            'workdays_paid' => 10,
            'workdays_volunteer' => 20,
        ];

        /**
         * It's important to check the admin user is not in the programme here,
         * otherwise the test would pass by using the regular policy, not the
         * admin one.
         */
        $this->assertDatabaseMissing('programme_user', [
            'programme_id' => 1,
            'user_id' => 2,
        ]);

        $this->patchJson('/api/programme/submission/1', $data, $headers)
            ->assertJsonFragment($data)
            ->assertStatus(200);
    }

    public function testUpdateAdditionalTreeSpeciesAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $data = [
            'title' => 'a new title',
            'technical_narrative' => 'a new test tech narrative',
            'workdays_paid' => 4,
            'workdays_volunteer' => 12,
            'additional_tree_species' => $uploadResponse->json('data.id'),
        ];

        $response = $this->patchJson('/api/programme/submission/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }

    public function testApproveAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $this->getJson('/api/programme/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => Submission::STATUS_AWAITING_APPROVAL,
            ]);

        $response = $this->patchJson('/api/programme/submission/1/approve', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => Submission::STATUS_APPROVED,
        ]);
    }

    public function testApproveForbiddenAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/programme/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => Submission::STATUS_AWAITING_APPROVAL,
            ]);

        $response = $this->patchJson('/api/programme/submission/1/approve', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(403);

        $this->getJson('/api/programme/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => Submission::STATUS_AWAITING_APPROVAL,
            ]);
    }

    public function testUpdatingCreatedByInUpdateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [
            'created_by' => 'a new user',
        ];

        $response = $this->patchJson('/api/programme/submission/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'created_by' => 'a new user',
        ]);
    }

    public function testReadByProgrammeAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/programme/1/submissions', $headers)
        ->assertStatus(200)
        ->assertJsonPath('data.0.id', 1);
    }

    public function testReadByProgrammeActionRequiresBelongingToProgramme(): void
    {
        $headers = $this->getHeaders('sue@example.com', 'Password123');

        $this->getJson('/api/programme/1/submissions', $headers)
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/programme/submission/1', $headers)
        ->assertStatus(200)
        ->assertJsonPath('data.id', 1);
    }

    public function testReadActionRequiresBelongingToProgramme(): void
    {
        $headers = $this->getHeaders('sue@example.com', 'Password123');

        $this->getJson('/api/programme/submission/1', $headers)
        ->assertStatus(403);
    }
}
