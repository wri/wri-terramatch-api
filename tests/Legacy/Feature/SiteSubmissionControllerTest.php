<?php

namespace Tests\Legacy\Feature;

use App\Models\DueSubmission;
use App\Models\Site;
use App\Models\SiteSubmission;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class SiteSubmissionControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_type' => Site::class,
            'due_submissionable_id' => 1,
        ]);

        $data = [
            'site_id' => 1,
            'created_by' => 'test user 3',
            'direct_seeding_kg' => 12,
            'due_submission_id' => $dueSubmission->id,
            'technical_narrative' => 'Some technical narrative',
            'workdays_paid' => 5,
            'workdays_volunteer' => 8,
        ];

        $response = $this->postJson('/api/site/submission', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'site_id' => 1,
            'direct_seeding_kg' => 12,
            'workdays_paid' => 5,
            'workdays_volunteer' => 8,
            'total_workdays' => 13,
        ]);
    }

    public function testUpdateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'direct_seeding_kg' => 1232,
            'workdays_paid' => 2,
            'workdays_volunteer' => 6,
        ];

        $response = $this->patchJson('/api/site/submission/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }

    public function testUpdateAdditionalTreeSpeciesAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $data = [
            'direct_seeding_kg' => 1232,
            'workdays_paid' => 2,
            'workdays_volunteer' => 6,
            'additional_tree_species' => $uploadResponse->json('data.id'),
        ];

        $response = $this->patchJson('/api/site/submission/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }

    public function testApproveAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $this->getJson('/api/site/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => SiteSubmission::STATUS_AWAITING_APPROVAL,
            ]);

        $response = $this->patchJson('/api/site/submission/1/approve', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => SiteSubmission::STATUS_APPROVED,
        ]);
    }

    public function testApproveForbiddenAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/site/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => SiteSubmission::STATUS_AWAITING_APPROVAL,
            ]);

        $response = $this->patchJson('/api/site/submission/1/approve', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(403);

        $this->getJson('/api/site/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => SiteSubmission::STATUS_AWAITING_APPROVAL,
            ]);
    }

    public function testUpdatingCreatedByInUpdateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'created_by' => 'a new user',
        ];

        $response = $this->patchJson('/api/site/submission/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'created_by' => 'a new user',
        ]);
    }

    public function testCreateActionRequiresSiteId(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $data = [];

        $this->postJson('/api/site/submission', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(422);
    }

    public function testCreateActionRequiresAccessToSite(): void
    {
        $headers = $this->getHeaders('sue@example.com', 'Password123');
        $data = [
            'site_id' => 1,
            'created_by' => 'test user 7',
        ];

        $this->postJson('/api/site/submission', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(403);
    }

    public function testReadAllBySiteAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/site/1/submissions', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
            ])
            ->assertJsonFragment([
                'id' => 2,
            ]);
    }

    public function testReadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/site/submission/1', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'site_id' => 1,
            ]);
    }
}
