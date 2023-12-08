<?php

namespace Tests\Legacy\Feature;

use App\Models\DocumentFile;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class DocumentFileControllerTest extends LegacyTestCase
{
    public function testCreateDocumentOnSubmissionsAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/document_files/file', [
            'document_fileable_id' => 1,
            'document_fileable_type' => 'submission',
            'upload' => $uploadResponse->json('data.id'),
            'title' => 'test csv file',
            'collection' => '',
            'is_public' => false,
        ], $headers)
            ->assertStatus(201);

        $response = $this->getJson('/api/programme/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.id', 1)
            ->assertJsonCount(2, 'data.document_files');
    }

    public function testCreateDocumentOnSiteSubmissionsAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        Queue::fake();

        for ($i = 1 ; $i <= 2 ; $i++) {
            $uploadResponse = $this->post('/api/uploads', [
                'upload' => $this->fakeValidCsv(),
            ], $headers);

            $this->postJson('/api/document_files/file', [
                'document_fileable_id' => 1,
                'document_fileable_type' => 'site_submission',
                'upload' => $uploadResponse->json('data.id'),
                'title' => "test csv file $i",
                'collection' => 'testing',
                'is_public' => false,
            ], $headers)
                ->assertStatus(201);
        }

        $response = $this->getJson('/api/site/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.id', 1)
            ->assertJsonCount(3, 'data.document_files');
    }

    public function testUpdateDocumentAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $docResponse = $this->postJson('/api/document_files/file', [
                'document_fileable_id' => 1,
                'document_fileable_type' => 'site_submission',
                'upload' => $uploadResponse->json('data.id'),
                'title' => 'test csv file 1',
                'collection' => 'testing',
                'is_public' => false,
            ], $headers)
            ->assertStatus(201);

        $this->putJson('/api/document_files/' . $docResponse->json('data.uuid'), ['title' => 'new title'], $headers)
            ->assertSuccessful()
            ->assertJsonFragment([
                'title' => 'new title',
                'type' => 'file',
                'collection' => 'testing',
                'is_public' => false,
            ]);
    }

    public function testCreateDraftDocumentForSubmissionsAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        Queue::fake();

        $data = [
            'name' => 'testing site submission',
            'type' => 'site_submission',
            'due_submission_id' => 2,
            'is_from_mobile' => false,
        ];

        $draftResponse = $this->postJson('/api/drafts', $data, $headers)
            ->assertStatus(201)
            ->assertJsonPath('data.id', 18);

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/document_files/file', [
            'document_fileable_id' => 1,
            'document_fileable_type' => 'submission',
            'upload' => $uploadResponse->json('data.id'),
            'title' => 'test csv file',
            'collection' => 'testing',
            'is_public' => false,
        ], $headers)
            ->assertStatus(201);
    }

    public function testDeleteDocumentFileAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/document_files/file', [
            'document_fileable_id' => 1,
            'document_fileable_type' => 'site_submission',
            'upload' => $uploadResponse->json('data.id'),
            'title' => 'file for delete test',
            'collection' => 'to-be-deleted',
            'is_public' => false,
        ], $headers)
                ->assertStatus(201);

        $this->getJson('/api/site/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.id', 1)
            ->assertJsonCount(2, 'data.document_files');

        $file = DocumentFile::where('collection', '=', 'to-be-deleted')->first();

        $this->deleteJson('/api/document_files/' . $file->uuid, $headers)
            ->assertStatus(200)
            ->assertJsonFragment(['data' => ['delete request has been processed']]);

        $this->getJson('/api/site/submission/1', $headers)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.document_files');
    }
}
