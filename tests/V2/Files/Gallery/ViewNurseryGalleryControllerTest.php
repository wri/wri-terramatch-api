<?php

namespace Tests\V2\Files\Gallery;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
//use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViewNurseryGalleryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $nursery;

    private $media;

    private $document;

    private $nurseryReportMedia;

    private $nurseryReportDocument;

    public function setUp(): void
    {
        parent::setUp();

        //        Artisan::call('v2migration:roles --fresh');
        $this->admin = User::factory()->admin()->create();
        $this->admin->givePermissionTo('framework-ppc');

        $this->nursery = Nursery::factory()
            ->has(NurseryReport::factory()->ppc(), 'reports')
            ->ppc()
            ->create();
        $nurseryReport = $this->nursery->reports()->first();

        Storage::fake('uploads');

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $nurseryReportMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $nurseryReportDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->nursery->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $document = $this->nursery->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->save();

        $nurseryReportMedia = $nurseryReport->addMedia($nurseryReportMedia)->toMediaCollection('photos');
        $nurseryReportMedia->lat = 56.32664;
        $nurseryReportMedia->lng = -75.27580;
        $nurseryReportMedia->file_type = 'media';
        $nurseryReportMedia->is_public = true;
        $nurseryReportMedia->save();

        $nurseryReportDocument = $nurseryReport->addMedia($nurseryReportDocument)->toMediaCollection('file');
        $nurseryReportDocument->mime_type = 'text/plain';
        $nurseryReportDocument->file_type = 'documents';
        $nurseryReportDocument->save();

        $this->media = $media;
        $this->document = $document;

        $this->nurseryReportMedia = $nurseryReportMedia;
        $this->nurseryReportDocument = $nurseryReportDocument;
    }

    public function test_that_all_nursery_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'file_url' => $this->media->getFullUrl(),
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'file_name' => $this->media->file_name,
                'model_name' => 'nursery',
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
                'model_name' => 'nursery',
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

    public function test_that_all_nursery_report_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment([
                'uuid' => $this->nurseryReportMedia->uuid,
                'file_url' => $this->nurseryReportMedia->getFullUrl(),
                'thumb_url' => $this->nurseryReportMedia->getFullUrl('thumbnail'),
                'file_name' => $this->nurseryReportMedia->file_name,
                'model_name' => 'nursery-report',
                'created_date' => $this->nurseryReportMedia->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => $this->nurseryReportMedia->lat,
                    'lng' => $this->nurseryReportMedia->lng,
                ],
                'mime_type' => 'image/png',
                'file_size' => $this->nurseryReportMedia->size,
            ])
            ->assertJsonFragment([
                'uuid' => $this->nurseryReportDocument->uuid,
                'file_url' => $this->nurseryReportDocument->getFullUrl(),
                'thumb_url' => $this->nurseryReportDocument->getFullUrl('thumbnail'),
                'file_name' => $this->nurseryReportDocument->file_name,
                'model_name' => 'nursery-report',
                'created_date' => $this->nurseryReportDocument->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->nurseryReportDocument->size,
            ]);
    }

    public function test_that_all_nursery_and_nursery_report_media_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files?filter[file_type]=media')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'model_name' => 'nursery',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'nursery-report',
                'mime_type' => 'image/png',
            ]);
    }

    public function test_that_all_nursery_and_nursery_report_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files?filter[file_type]=documents')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'model_name' => 'nursery',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'nursery-report',
                'mime_type' => 'text/plain',
            ]);
    }

    public function test_that_nursery_and_nursery_report_files_are_paginated()
    {
        for ($index = 0; $index < 15; $index++) {
            $media = $this->nursery->addMedia(UploadedFile::fake()->image('cover.png', 10, 10))->toMediaCollection('media');
            $media->file_type = 'media';
            $media->save();
        }

        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files?filter[file_type]=media&page=1')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data');

        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files?filter[file_type]=media&page=2')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    }

    public function test_that_only_specific_model_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files?filter[file_type]=documents&model_name=nurseries')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'nursery',
                'mime_type' => 'text/plain',
            ]);

        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/files?filter[file_type]=documents&model_name=nursery-reports')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'nursery-report',
                'mime_type' => 'text/plain',
            ]);
    }
}
