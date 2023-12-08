<?php

namespace Tests\Legacy\Feature;

use App\Jobs\CreateProgrammeTreeSpeciesJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class ProgrammeTreeSpeciesCsvControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        Queue::fake();

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/programme/tree_species/csv', [
            'programme_id' => 1,
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'completed_rows' => 0,
            'total_rows' => 33,
        ]);

        Queue::assertNotPushed(CreateProgrammeTreeSpeciesJob::class, function ($job) {
            return $job->getName() === 'Tree Species';
        });

        Queue::assertPushed(CreateProgrammeTreeSpeciesJob::class, function ($job) {
            return $job->getName() === 'Tree species 01';
        });
    }

    public function testCreateActionFileIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/programme/tree_species/csv', [
            'programme_id' => 1,
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionProgrammeIdIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/programme/tree_species/csv', [
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(404);
    }

    public function testCreateActionRequiresBelongingToTreeSpeciesProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/programme/tree_species/csv', [
            'programme_id' => 1,
            'file' => $this->fakeValidCsv(),
        ], $headers)
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->getJson('/api/programme/tree_species/csv/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'completed_rows' => 1,
                'total_rows' => 10,
                'status' => 'pending',
            ]);
    }

    public function testReadTreeSpeciesAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->getJson('/api/programme/tree_species/csv/1/trees', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 2,
                'name' => 'A tree species',
                'programme_id' => 1,
            ]);
    }

    public function testDownloadCsvTemplateAction(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/uploads/socioeconomic_benefits/template/csv', $headers)
            ->assertStatus(200);
    }
}
