<?php

namespace Tests\V2\Files\Location;

use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteReportImageLocationsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $siteReport;

    private $media;

    private $mediaWithNoLocation;

    private $document;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('v2migration:roles');
        $this->admin = User::factory()->admin()->create();
        $this->admin->givePermissionTo('framework-ppc');

        $this->siteReport = SiteReport::factory()->ppc()->create();

        Storage::fake('uploads');

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $imageWithNoLocationData = UploadedFile::fake()->image('site_report_image_with_no_location.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->siteReport->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $imageWithNoLocationData = $this->siteReport->addMedia($imageWithNoLocationData)->toMediaCollection('photos');
        $imageWithNoLocationData->lng = 0;
        $imageWithNoLocationData->file_type = 'media';
        $imageWithNoLocationData->save();

        $document = $this->siteReport->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->save();

        $this->media = $media;
        $this->mediaWithNoLocation = $imageWithNoLocationData;
        $this->document = $document;
    }

    public function test_that_all_site_report_media_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/site-reports/' . $this->siteReport->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->media->lat,
                    'lng' => $this->media->lng,
                ],
            ]);
    }

    public function test_that_all_site_report_media_with_no_location_data_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/site-reports/' . $this->siteReport->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissing([
                'uuid' => $this->mediaWithNoLocation->uuid,
                'thumb_url' => $this->mediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->mediaWithNoLocation->lat,
                    'lng' => $this->mediaWithNoLocation->lng,
                ],
            ]);
    }

    public function test_that_site_report_documents_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/site-reports/' . $this->siteReport->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissing([
                'uuid' => $this->document->uuid,
                'thumb_url' => $this->document->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->document->lat,
                    'lng' => $this->document->lng,
                ],
            ]);
    }
}
