<?php

namespace Tests\V2\Files\Gallery;

use App\Models\V2\User;
use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViewProjectMonitoringGalleryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $projectMonitoring;

    private $media;

    private $document;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('v2migration:roles');
        $this->admin = User::factory()->admin()->create();
        $this->admin->givePermissionTo('framework-ppc');

        $this->projectMonitoring = ProjectMonitoring::factory()->ppc()->create();

        Storage::fake('uploads');

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->projectMonitoring->addMedia($image)->toMediaCollection('media');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $document = $this->projectMonitoring->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->save();

        $this->media = $media;
        $this->document = $document;
    }

    public function test_that_all_project_monitoring_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/project-monitorings/' . $this->projectMonitoring->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'file_url' => $this->media->getFullUrl(),
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'file_name' => $this->media->file_name,
                'model_name' => 'project-monitoring',
                'created_date' => $this->media->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => $this->media->lat,
                    'lng' => $this->media->lng,
                ],
                'mime_type' => 'image/png',
                'file_size' => $this->media->size,
                'collection_name' => $this->media->collection_name,
            ])
            ->assertJsonFragment([
                'uuid' => $this->document->uuid,
                'file_url' => $this->document->getFullUrl(),
                'thumb_url' => $this->document->getFullUrl('thumbnail'),
                'file_name' => $this->document->file_name,
                'model_name' => 'project-monitoring',
                'created_date' => $this->document->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->document->size,
                'collection_name' => $this->document->collection_name,
            ]);
    }

    public function test_that_all_project_monitoring_media_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/project-monitorings/' . $this->projectMonitoring->uuid . '/files?filter[file_type]=media')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['mime_type' => 'image/png']);
    }

    public function test_that_all_project_monitoring_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/project-monitorings/' . $this->projectMonitoring->uuid . '/files?filter[file_type]=documents')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['mime_type' => 'text/plain']);
    }

    public function test_that_project_monitoring_files_are_paginated()
    {
        for ($index = 0; $index < 15; $index++) {
            $media = $this->projectMonitoring->addMedia(UploadedFile::fake()->image('cover.png', 10, 10))->toMediaCollection('media');
            $media->file_type = 'media';
            $media->save();
        }

        $this->actingAs($this->admin)
            ->getJson('/api/v2/project-monitorings/' . $this->projectMonitoring->uuid . '/files?filter[file_type]=media&page=1')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data');

        $this->actingAs($this->admin)
            ->getJson('/api/v2/project-monitorings/' . $this->projectMonitoring->uuid . '/files?filter[file_type]=media&page=2')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    }
}
