<?php

namespace Tests\V2\Files\Gallery;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViewProjectGalleryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $project;

    private $media;

    private $document;

    private $projectReportMedia;

    private $projectReportDocument;

    private $siteMedia;

    private $siteDocument;

    private $nurseryMedia;

    private $nurseryDocument;

    private $siteReportMedia;

    private $siteReportDocument;

    private $nurseryReportMedia;

    private $nurseryReportDocument;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->ppcAdmin()->create();

        $this->project = Project::factory()
            ->has(ProjectReport::factory()->ppc(), 'reports')
            ->has(
                Site::factory()
                    ->has(SiteReport::factory()->ppc(), 'reports')
                    ->ppc()
            )
            ->has(
                Nursery::factory()
                    ->has(NurseryReport::factory()->ppc(), 'reports')
                    ->ppc()
            )
            ->ppc()
            ->create();
        $projectReport = $this->project->reports()->first();
        $sites = $this->project->sites()->first();
        $nursery = $this->project->nurseries()->first();
        $siteReport = $sites->reports()->first();
        $nurseryReport = $nursery->reports()->first();

        Storage::fake('uploads');

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $projectReportMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $projectReportDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $siteMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $siteDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $nurseryMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $nurseryDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $siteReportsMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $siteReportsDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $nurseryReportMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $nurseryReportDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->project->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $document = $this->project->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->save();

        $projectReportMedia = $projectReport->addMedia($projectReportMedia)->toMediaCollection('photos');
        $projectReportMedia->lat = 56.32664;
        $projectReportMedia->lng = -75.27580;
        $projectReportMedia->file_type = 'media';
        $projectReportMedia->is_public = true;
        $projectReportMedia->save();

        $projectReportDocument = $projectReport->addMedia($projectReportDocument)->toMediaCollection('file');
        $projectReportDocument->mime_type = 'text/plain';
        $projectReportDocument->file_type = 'documents';
        $projectReportDocument->save();

        $siteMedia = $sites->addMedia($siteMedia)->toMediaCollection('photos');
        $siteMedia->lat = 56.32664;
        $siteMedia->lng = -75.27580;
        $siteMedia->file_type = 'media';
        $siteMedia->is_public = true;
        $siteMedia->save();

        $siteDocument = $sites->addMedia($siteDocument)->toMediaCollection('file');
        $siteDocument->mime_type = 'text/plain';
        $siteDocument->file_type = 'documents';
        $siteDocument->save();

        $nurseryMedia = $nursery->addMedia($nurseryMedia)->toMediaCollection('photos');
        $nurseryMedia->lat = 56.32664;
        $nurseryMedia->lng = -75.27580;
        $nurseryMedia->file_type = 'media';
        $nurseryMedia->is_public = true;
        $nurseryMedia->save();

        $nurseryDocument = $nursery->addMedia($nurseryDocument)->toMediaCollection('file');
        $nurseryDocument->mime_type = 'text/plain';
        $nurseryDocument->file_type = 'documents';
        $nurseryDocument->save();

        $siteReportsMedia = $siteReport->addMedia($siteReportsMedia)->toMediaCollection('photos');
        $siteReportsMedia->lat = 56.32664;
        $siteReportsMedia->lng = -75.27580;
        $siteReportsMedia->file_type = 'media';
        $siteReportsMedia->is_public = true;
        $siteReportsMedia->save();

        $siteReportsDocument = $siteReport->addMedia($siteReportsDocument)->toMediaCollection('file');
        $siteReportsDocument->mime_type = 'text/plain';
        $siteReportsDocument->file_type = 'documents';
        $siteReportsDocument->save();

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

        $this->projectReportMedia = $projectReportMedia;
        $this->projectReportDocument = $projectReportDocument;

        $this->siteMedia = $siteMedia;
        $this->siteDocument = $siteDocument;

        $this->nurseryMedia = $nurseryMedia;
        $this->nurseryDocument = $nurseryDocument;

        $this->siteReportMedia = $siteReportsMedia;
        $this->siteReportDocument = $siteReportsDocument;

        $this->nurseryReportMedia = $nurseryReportMedia;
        $this->nurseryReportDocument = $nurseryReportDocument;
    }

    public function test_that_all_project_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'file_url' => $this->media->getFullUrl(),
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'file_name' => $this->media->file_name,
                'model_name' => 'project',
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
                'model_name' => 'project',
                'created_date' => $this->document->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->document->size,
                'collection_name' => $this->media->collection_name,
            ]);
    }

    public function test_that_all_project_report_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data')
            ->assertJsonFragment([
                'uuid' => $this->projectReportMedia->uuid,
                'file_url' => $this->projectReportMedia->getFullUrl(),
                'thumb_url' => $this->projectReportMedia->getFullUrl('thumbnail'),
                'file_name' => $this->projectReportMedia->file_name,
                'model_name' => 'project-report',
                'created_date' => $this->projectReportMedia->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => $this->projectReportMedia->lat,
                    'lng' => $this->projectReportMedia->lng,
                ],
                'mime_type' => 'image/png',
                'file_size' => $this->projectReportMedia->size,
                'collection_name' => $this->projectReportMedia->collection_name,
            ])
            ->assertJsonFragment([
                'uuid' => $this->projectReportDocument->uuid,
                'file_url' => $this->projectReportDocument->getFullUrl(),
                'thumb_url' => $this->projectReportDocument->getFullUrl('thumbnail'),
                'file_name' => $this->projectReportDocument->file_name,
                'model_name' => 'project-report',
                'created_date' => $this->projectReportDocument->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->projectReportDocument->size,
                'collection_name' => $this->projectReportDocument->collection_name,
            ]);
    }

    public function test_that_all_site_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data')
            ->assertJsonFragment([
                'uuid' => $this->siteMedia->uuid,
                'file_url' => $this->siteMedia->getFullUrl(),
                'thumb_url' => $this->siteMedia->getFullUrl('thumbnail'),
                'file_name' => $this->siteMedia->file_name,
                'model_name' => 'site',
                'created_date' => $this->siteMedia->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => $this->siteMedia->lat,
                    'lng' => $this->siteMedia->lng,
                ],
                'mime_type' => 'image/png',
                'file_size' => $this->siteMedia->size,
                'collection_name' => $this->siteMedia->collection_name,
            ])
            ->assertJsonFragment([
                'uuid' => $this->siteDocument->uuid,
                'file_url' => $this->siteDocument->getFullUrl(),
                'thumb_url' => $this->siteDocument->getFullUrl('thumbnail'),
                'file_name' => $this->siteDocument->file_name,
                'model_name' => 'site',
                'created_date' => $this->siteDocument->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->siteDocument->size,
                'collection_name' => $this->siteDocument->collection_name,
            ]);
    }

    public function test_that_all_nursery_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data')
            ->assertJsonFragment([
                'uuid' => $this->nurseryMedia->uuid,
                'file_url' => $this->nurseryMedia->getFullUrl(),
                'thumb_url' => $this->nurseryMedia->getFullUrl('thumbnail'),
                'file_name' => $this->nurseryMedia->file_name,
                'model_name' => 'nursery',
                'created_date' => $this->nurseryMedia->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => $this->nurseryMedia->lat,
                    'lng' => $this->nurseryMedia->lng,
                ],
                'mime_type' => 'image/png',
                'file_size' => $this->nurseryMedia->size,
                'collection_name' => $this->nurseryMedia->collection_name,
            ])
            ->assertJsonFragment([
                'uuid' => $this->nurseryDocument->uuid,
                'file_url' => $this->nurseryDocument->getFullUrl(),
                'thumb_url' => $this->nurseryDocument->getFullUrl('thumbnail'),
                'file_name' => $this->nurseryDocument->file_name,
                'model_name' => 'nursery',
                'created_date' => $this->nurseryDocument->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->nurseryDocument->size,
                'collection_name' => $this->nurseryDocument->collection_name,
            ]);
    }

    public function test_that_all_site_report_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data')
            ->assertJsonFragment([
                'uuid' => $this->siteReportMedia->uuid,
                'file_url' => $this->siteReportMedia->getFullUrl(),
                'thumb_url' => $this->siteReportMedia->getFullUrl('thumbnail'),
                'file_name' => $this->siteReportMedia->file_name,
                'model_name' => 'site-report',
                'created_date' => $this->siteReportMedia->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => $this->siteReportMedia->lat,
                    'lng' => $this->siteReportMedia->lng,
                ],
                'mime_type' => 'image/png',
                'file_size' => $this->siteReportMedia->size,
                'collection_name' => $this->siteReportMedia->collection_name,
            ])
            ->assertJsonFragment([
                'uuid' => $this->siteReportDocument->uuid,
                'file_url' => $this->siteReportDocument->getFullUrl(),
                'thumb_url' => $this->siteReportDocument->getFullUrl('thumbnail'),
                'file_name' => $this->siteReportDocument->file_name,
                'model_name' => 'site-report',
                'created_date' => $this->siteReportDocument->created_at,
                'is_public' => true,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->siteReportDocument->size,
                'collection_name' => $this->siteReportDocument->collection_name,
            ]);
    }

    public function test_that_all_nursery_report_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data')
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
                'collection_name' => $this->nurseryReportMedia->collection_name,
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
                'collection_name' => $this->nurseryReportDocument->collection_name,
            ]);
    }

    public function test_that_all_project_and_children_media_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=media')
            ->assertSuccessful()
            ->assertJsonCount(6, 'data')
            ->assertJsonFragment([
                'model_name' => 'project',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'nursery',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'project-report',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'site-report',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'nursery-report',
                'mime_type' => 'image/png',
            ]);
    }

    public function test_that_all_project_and_children_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=documents')
            ->assertSuccessful()
            ->assertJsonCount(6, 'data')
            ->assertJsonFragment([
                'model_name' => 'project',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'nursery',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'project-report',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'site-report',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'nursery-report',
                'mime_type' => 'text/plain',
            ]);
    }

    public function test_that_project_files_are_paginated()
    {
        for ($index = 0; $index < 15; $index++) {
            $media = $this->project->addMedia(UploadedFile::fake()->image('cover.png', 10, 10))->toMediaCollection('photos');
            $media->file_type = 'media';
            $media->save();
        }

        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=media&page=1')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data');

        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=media&page=2')
            ->assertSuccessful()
            ->assertJsonCount(6, 'data');
    }

    public function test_that_only_specific_model_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=documents&model_name=sites')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'text/plain',
            ]);

        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=documents&model_name=nursery-reports')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'nursery-report',
                'mime_type' => 'text/plain',
            ]);

        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/files?filter[file_type]=documents&model_name=projects')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'project',
                'mime_type' => 'text/plain',
            ]);
    }
}
