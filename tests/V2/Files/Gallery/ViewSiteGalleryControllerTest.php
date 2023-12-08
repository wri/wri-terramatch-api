<?php

namespace Tests\V2\Files\Gallery;

use App\Models\User;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
//use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViewSiteGalleryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $site;

    private $media;

    private $document;

    private $siteReportMedia;

    private $siteReportDocument;

    public function setUp(): void
    {
        parent::setUp();

        //        Artisan::call('v2migration:roles --fresh');
        $this->admin = User::factory()->admin()->create();
        $this->admin->givePermissionTo('framework-ppc');

        $this->site = Site::factory()
            ->has(SiteReport::factory()->ppc(), 'reports')
            ->ppc()
            ->create();
        $siteReport = $this->site->reports()->first();

        Storage::fake('uploads');

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $siteReportImage = UploadedFile::fake()->image('cover.png', 10, 10);
        $siteReportDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->site->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $document = $this->site->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->is_public = false;
        $document->save();

        $siteReportMedia = $siteReport->addMedia($siteReportImage)->toMediaCollection('photos');
        $siteReportMedia->lat = 56.32664;
        $siteReportMedia->lng = -75.27580;
        $siteReportMedia->file_type = 'media';
        $siteReportMedia->is_public = true;
        $siteReportMedia->save();

        $siteReportDocument = $siteReport->addMedia($siteReportDocument)->toMediaCollection('file');
        $siteReportDocument->mime_type = 'text/plain';
        $siteReportDocument->file_type = 'documents';
        $siteReportDocument->save();

        $this->media = $media;
        $this->document = $document;

        $this->siteReportMedia = $siteReportMedia;
        $this->siteReportDocument = $siteReportDocument;
    }

    public function test_that_all_site_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'file_url' => $this->media->getFullUrl(),
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'file_name' => $this->media->file_name,
                'model_name' => 'site',
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
                'model_name' => 'site',
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

    public function test_that_all_site_report_files_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files')
            ->assertSuccessful()
            ->assertJsonCount(4, 'data')
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
            ])
            ->assertJsonFragment([
                'uuid' => $this->siteReportDocument->uuid,
                'file_url' => $this->siteReportDocument->getFullUrl(),
                'thumb_url' => $this->siteReportDocument->getFullUrl('thumbnail'),
                'file_name' => $this->siteReportDocument->file_name,
                'model_name' => 'site-report',
                'created_date' => $this->siteReportDocument->created_at,
                'is_public' => false,
                'location' => [
                    'lat' => 0,
                    'lng' => 0,
                ],
                'mime_type' => 'text/plain',
                'file_size' => $this->siteReportDocument->size,
            ]);
    }

    public function test_that_all_site_and_site_report_media_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=media')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'model_name' => 'site-report',
                'mime_type' => 'image/png',
            ]);
    }

    public function test_that_all_site_and_site_report_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=documents')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'text/plain',
            ])
            ->assertJsonFragment([
                'model_name' => 'site-report',
                'mime_type' => 'text/plain',
            ]);

        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=documents&filter[is_public]=1')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'site-report',
                'mime_type' => 'text/plain',
            ]);

        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=documents&filter[is_public]=0')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'text/plain',
            ]);
    }

    public function test_that_site_and_site_report_files_are_paginated()
    {
        for ($index = 0; $index < 15; $index++) {
            $media = $this->site->addMedia(UploadedFile::fake()->image('cover.png', 10, 10))->toMediaCollection('media');
            $media->file_type = 'media';
            $media->save();
        }

        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=media&page=1')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data');

        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=media&page=2')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    }

    public function test_that_only_specific_model_document_files_are_retrieved_when_filter_is_given()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=documents&model_name=site-reports')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'site-report',
                'mime_type' => 'text/plain',
            ]);

        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/files?filter[file_type]=documents&model_name=sites')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'model_name' => 'site',
                'mime_type' => 'text/plain',
            ]);
    }
}
