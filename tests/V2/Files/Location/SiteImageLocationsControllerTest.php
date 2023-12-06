<?php

namespace Tests\V2\Files\Location;

use App\Models\User;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SiteImageLocationsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $site;

    private $media;

    private $siteReportMedia;

    private $mediaWithNoLocation;

    private $siteReportMediaWithNoLocation;

    private $document;

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

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $imageWithNoLocationData = UploadedFile::fake()->image('site_image_with_no_location.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $siteReportsMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $siteReportMediaWithNoLocationData = UploadedFile::fake()->image('site_report_image_with_no_location.png', 10, 10);
        $siteReportsDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->site->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $imageWithNoLocationData = $this->site->addMedia($imageWithNoLocationData)->toMediaCollection('photos');
        $imageWithNoLocationData->lng = 0;
        $imageWithNoLocationData->file_type = 'media';
        $imageWithNoLocationData->save();

        $document = $this->site->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->save();

        $siteReportsMedia = $siteReport->addMedia($siteReportsMedia)->toMediaCollection('photos');
        $siteReportsMedia->lat = 56.32664;
        $siteReportsMedia->lng = -75.27580;
        $siteReportsMedia->file_type = 'media';
        $siteReportsMedia->is_public = true;
        $siteReportsMedia->save();

        $siteReportMediaWithNoLocationData = $siteReport->addMedia($siteReportMediaWithNoLocationData)->toMediaCollection('photos');
        $siteReportMediaWithNoLocationData->lng = 0;
        $siteReportMediaWithNoLocationData->file_type = 'media';
        $siteReportMediaWithNoLocationData->save();

        $siteReportsDocument = $siteReport->addMedia($siteReportsDocument)->toMediaCollection('file');
        $siteReportsDocument->mime_type = 'text/plain';
        $siteReportsDocument->file_type = 'documents';
        $siteReportsDocument->save();

        $this->media = $media;
        $this->mediaWithNoLocation = $imageWithNoLocationData;
        $this->document = $document;

        $this->siteReportMedia = $siteReportsMedia;
        $this->siteReportMediaWithNoLocation = $siteReportMediaWithNoLocationData;
        $this->siteReportDocument = $siteReportsDocument;
    }

    public function test_that_all_site_and_site_report_media_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->media->lat,
                    'lng' => $this->media->lng,
                ],
            ])
            ->assertJsonFragment([
                'uuid' => $this->siteReportMedia->uuid,
                'thumb_url' => $this->siteReportMedia->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteReportMedia->lat,
                    'lng' => $this->siteReportMedia->lng,
                ],
            ]);
    }

    public function test_that_all_site_and_site_report_media_with_no_location_data_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing([
                'uuid' => $this->mediaWithNoLocation->uuid,
                'thumb_url' => $this->mediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->mediaWithNoLocation->lat,
                    'lng' => $this->mediaWithNoLocation->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->siteReportMediaWithNoLocation->uuid,
                'thumb_url' => $this->siteReportMediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteReportMediaWithNoLocation->lat,
                    'lng' => $this->siteReportMediaWithNoLocation->lng,
                ],
            ]);
    }

    public function test_that_site_and_site_report_documents_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing([
                'uuid' => $this->document->uuid,
                'thumb_url' => $this->document->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->document->lat,
                    'lng' => $this->document->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->siteReportDocument->uuid,
                'thumb_url' => $this->siteReportDocument->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteReportDocument->lat,
                    'lng' => $this->siteReportDocument->lng,
                ],
            ]);
    }
}
