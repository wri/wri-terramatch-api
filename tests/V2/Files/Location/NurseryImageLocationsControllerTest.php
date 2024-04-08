<?php

namespace Tests\V2\Files\Location;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NurseryImageLocationsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $nursery;

    private $media;

    private $nurseryReportMedia;

    private $mediaWithNoLocation;

    private $nurseryReportMediaWithNoLocation;

    private $document;

    private $nurseryReportDocument;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('v2migration:roles');
        $this->admin = User::factory()->admin()->create();
        $this->admin->givePermissionTo('framework-ppc');

        $this->nursery = Nursery::factory()
            ->has(NurseryReport::factory()->ppc(), 'reports')
            ->ppc()
            ->create();
        $nurseryReport = $this->nursery->reports()->first();

        $image = UploadedFile::fake()->image('cover.png', 10, 10);
        $imageWithNoLocationData = UploadedFile::fake()->image('nursery_image_with_no_location.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $nurseryReportsMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $nurseryReportMediaWithNoLocationData = UploadedFile::fake()->image('nursery_report_image_with_no_location.png', 10, 10);
        $nurseryReportsDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->nursery->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $imageWithNoLocationData = $this->nursery->addMedia($imageWithNoLocationData)->toMediaCollection('photos');
        $imageWithNoLocationData->lng = 0;
        $imageWithNoLocationData->file_type = 'media';
        $imageWithNoLocationData->save();

        $document = $this->nursery->addMedia($document)->toMediaCollection('file');
        $document->mime_type = 'text/plain';
        $document->file_type = 'documents';
        $document->save();

        $nurseryReportsMedia = $nurseryReport->addMedia($nurseryReportsMedia)->toMediaCollection('photos');
        $nurseryReportsMedia->lat = 56.32664;
        $nurseryReportsMedia->lng = -75.27580;
        $nurseryReportsMedia->file_type = 'media';
        $nurseryReportsMedia->is_public = true;
        $nurseryReportsMedia->save();

        $nurseryReportMediaWithNoLocationData = $nurseryReport->addMedia($nurseryReportMediaWithNoLocationData)->toMediaCollection('photos');
        $nurseryReportMediaWithNoLocationData->lng = 0;
        $nurseryReportMediaWithNoLocationData->file_type = 'media';
        $nurseryReportMediaWithNoLocationData->save();

        $nurseryReportsDocument = $nurseryReport->addMedia($nurseryReportsDocument)->toMediaCollection('file');
        $nurseryReportsDocument->mime_type = 'text/plain';
        $nurseryReportsDocument->file_type = 'documents';
        $nurseryReportsDocument->save();

        $this->media = $media;
        $this->mediaWithNoLocation = $imageWithNoLocationData;
        $this->document = $document;

        $this->nurseryReportMedia = $nurseryReportsMedia;
        $this->nurseryReportMediaWithNoLocation = $nurseryReportMediaWithNoLocationData;
        $this->nurseryReportDocument = $nurseryReportsDocument;
    }

    public function test_that_all_nursery_and_nursery_report_media_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/image/locations')
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
                'uuid' => $this->nurseryReportMedia->uuid,
                'thumb_url' => $this->nurseryReportMedia->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->nurseryReportMedia->lat,
                    'lng' => $this->nurseryReportMedia->lng,
                ],
            ]);
    }

    public function test_that_all_site_and_site_report_media_with_no_location_data_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/image/locations')
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
                'uuid' => $this->nurseryReportMediaWithNoLocation->uuid,
                'thumb_url' => $this->nurseryReportMediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->nurseryReportMediaWithNoLocation->lat,
                    'lng' => $this->nurseryReportMediaWithNoLocation->lng,
                ],
            ]);
    }

    public function test_that_site_and_site_report_documents_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/nurseries/' . $this->nursery->uuid . '/image/locations')
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
                'uuid' => $this->nurseryReportDocument->uuid,
                'thumb_url' => $this->nurseryReportDocument->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->nurseryReportDocument->lat,
                    'lng' => $this->nurseryReportDocument->lng,
                ],
            ]);
    }
}
