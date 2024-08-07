<?php

namespace Tests\V2\Files\Location;

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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectImageLocationsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $admin;

    private $project;

    private $media;

    private $projectReportMedia;

    private $siteMedia;

    private $nurseryMedia;

    private $siteReportMedia;

    private $nurseryReportMedia;

    private $mediaWithNoLocation;

    private $projectReportMediaWithNoLocation;

    private $siteMediaWithNoLocation;

    private $nurseryMediaWithNoLocation;

    private $siteReportMediaWithNoLocation;

    private $nurseryReportMediaWithNoLocation;

    private $document;

    private $projectReportDocument;

    private $siteDocument;

    private $nurseryDocument;

    private $siteReportDocument;

    private $nurseryReportDocument;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('v2migration:roles');
        $this->admin = User::factory()->admin()->create();
        $this->admin->givePermissionTo('framework-ppc');

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
        $imageWithNoLocationData = UploadedFile::fake()->image('project_image_with_no_location.png', 10, 10);
        $document = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $projectReportMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $projectReportMediaWithNoLocationData = UploadedFile::fake()->image('project_report_image_with_no_location.png', 10, 10);
        $projectReportDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $siteMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $siteWithNoLocationData = UploadedFile::fake()->image('site_image_with_no_location.png', 10, 10);
        $siteDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $nurseryMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $nurseryMediaWithNoLocationData = UploadedFile::fake()->image('nursery_image_with_no_location.png', 10, 10);
        $nurseryDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $siteReportsMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $siteReportMediaWithNoLocationData = UploadedFile::fake()->image('site_report_image_with_no_location.png', 10, 10);
        $siteReportsDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $nurseryReportMedia = UploadedFile::fake()->image('cover.png', 10, 10);
        $nurseryReportMediaWithNoLocationData = UploadedFile::fake()->image('nursery_report_image_with_no_location.png', 10, 10);
        $nurseryReportDocument = UploadedFile::fake()->create('test_file.txt', 10, 'text/plain');

        $media = $this->project->addMedia($image)->toMediaCollection('photos');
        $media->lat = 56.32664;
        $media->lng = -75.27580;
        $media->file_type = 'media';
        $media->is_public = true;
        $media->save();

        $imageWithNoLocationData = $this->project->addMedia($imageWithNoLocationData)->toMediaCollection('photos');
        $imageWithNoLocationData->lng = 0;
        $imageWithNoLocationData->file_type = 'media';
        $imageWithNoLocationData->save();

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

        $projectReportMediaWithNoLocationData = $projectReport->addMedia($projectReportMediaWithNoLocationData)->toMediaCollection('photos');
        $projectReportMediaWithNoLocationData->lat = 0;
        $projectReportMediaWithNoLocationData->file_type = 'media';
        $projectReportMediaWithNoLocationData->save();

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

        $siteWithNoLocationData = $sites->addMedia($siteWithNoLocationData)->toMediaCollection('photos');
        $siteWithNoLocationData->lat = 0;
        $siteWithNoLocationData->file_type = 'media';
        $siteWithNoLocationData->save();

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

        $nurseryMediaWithNoLocationData = $nursery->addMedia($nurseryMediaWithNoLocationData)->toMediaCollection('photos');
        $nurseryMediaWithNoLocationData->lat = 0;
        $nurseryMediaWithNoLocationData->file_type = 'media';
        $nurseryMediaWithNoLocationData->save();

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

        $siteReportMediaWithNoLocationData = $siteReport->addMedia($siteReportMediaWithNoLocationData)->toMediaCollection('photos');
        $siteReportMediaWithNoLocationData->lng = 0;
        $siteReportMediaWithNoLocationData->file_type = 'media';
        $siteReportMediaWithNoLocationData->save();

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

        $nurseryReportMediaWithNoLocationData = $nurseryReport->addMedia($nurseryReportMediaWithNoLocationData)->toMediaCollection('photos');
        $nurseryReportMediaWithNoLocationData->lng = 0;
        $nurseryReportMediaWithNoLocationData->file_type = 'media';
        $nurseryReportMediaWithNoLocationData->save();

        $nurseryReportDocument = $nurseryReport->addMedia($nurseryReportDocument)->toMediaCollection('file');
        $nurseryReportDocument->mime_type = 'text/plain';
        $nurseryReportDocument->file_type = 'documents';
        $nurseryReportDocument->save();

        $this->media = $media;
        $this->mediaWithNoLocation = $imageWithNoLocationData;
        $this->document = $document;

        $this->projectReportMedia = $projectReportMedia;
        $this->projectReportMediaWithNoLocation = $projectReportMediaWithNoLocationData;
        $this->projectReportDocument = $projectReportDocument;

        $this->siteMedia = $siteMedia;
        $this->siteMediaWithNoLocation = $siteWithNoLocationData;
        $this->siteDocument = $siteDocument;

        $this->nurseryMedia = $nurseryMedia;
        $this->nurseryMediaWithNoLocation = $nurseryMediaWithNoLocationData;
        $this->nurseryDocument = $nurseryDocument;

        $this->siteReportMedia = $siteReportsMedia;
        $this->siteReportMediaWithNoLocation = $siteReportMediaWithNoLocationData;
        $this->siteReportDocument = $siteReportsDocument;

        $this->nurseryReportMedia = $nurseryReportMedia;
        $this->nurseryReportMediaWithNoLocation = $nurseryReportMediaWithNoLocationData;
        $this->nurseryReportDocument = $nurseryReportDocument;
    }

    public function test_that_all_project_and_children_media_are_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(6, 'data')
            ->assertJsonFragment([
                'uuid' => $this->media->uuid,
                'thumb_url' => $this->media->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->media->lat,
                    'lng' => $this->media->lng,
                ],
            ])
            ->assertJsonFragment([
                'uuid' => $this->projectReportMedia->uuid,
                'thumb_url' => $this->projectReportMedia->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->projectReportMedia->lat,
                    'lng' => $this->projectReportMedia->lng,
                ],
            ])
            ->assertJsonFragment([
                'thumb_url' => $this->siteMedia->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteMedia->lat,
                    'lng' => $this->siteMedia->lng,
                ],
            ])
            ->assertJsonFragment([
                'uuid' => $this->nurseryMedia->uuid,
                'thumb_url' => $this->nurseryMedia->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->nurseryMedia->lat,
                    'lng' => $this->nurseryMedia->lng,
                ],
            ])
            ->assertJsonFragment([
                'uuid' => $this->siteReportMedia->uuid,
                'thumb_url' => $this->siteReportMedia->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteReportMedia->lat,
                    'lng' => $this->siteReportMedia->lng,
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

    public function test_that_all_project_and_children_media_with_no_location_data_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(6, 'data')
            ->assertJsonMissing([
                'uuid' => $this->mediaWithNoLocation->uuid,
                'thumb_url' => $this->mediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->mediaWithNoLocation->lat,
                    'lng' => $this->mediaWithNoLocation->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->projectReportMediaWithNoLocation->uuid,
                'thumb_url' => $this->projectReportMediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->projectReportMediaWithNoLocation->lat,
                    'lng' => $this->projectReportMediaWithNoLocation->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->siteMediaWithNoLocation->uuid,
                'thumb_url' => $this->siteMediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteMediaWithNoLocation->lat,
                    'lng' => $this->siteMediaWithNoLocation->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->nurseryReportMediaWithNoLocation->uuid,
                'thumb_url' => $this->nurseryMediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->nurseryMediaWithNoLocation->lat,
                    'lng' => $this->nurseryMediaWithNoLocation->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->siteReportMediaWithNoLocation->uuid,
                'thumb_url' => $this->siteReportMediaWithNoLocation->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteReportMediaWithNoLocation->lat,
                    'lng' => $this->siteReportMediaWithNoLocation->lng,
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

    public function test_that_project_and_children_documents_are_not_retrieved()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/image/locations')
            ->assertSuccessful()
            ->assertJsonCount(6, 'data')
            ->assertJsonMissing([
                'uuid' => $this->document->uuid,
                'thumb_url' => $this->document->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->document->lat,
                    'lng' => $this->document->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->projectReportDocument->uuid,
                'thumb_url' => $this->projectReportDocument->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->projectReportDocument->lat,
                    'lng' => $this->projectReportDocument->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->siteDocument->uuid,
                'thumb_url' => $this->siteDocument->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteDocument->lat,
                    'lng' => $this->siteDocument->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->nurseryDocument->uuid,
                'thumb_url' => $this->nurseryDocument->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->nurseryDocument->lat,
                    'lng' => $this->nurseryDocument->lng,
                ],
            ])
            ->assertJsonMissing([
                'uuid' => $this->siteReportDocument->uuid,
                'thumb_url' => $this->siteReportDocument->getFullUrl('thumbnail'),
                'location' => [
                    'lat' => $this->siteReportDocument->lat,
                    'lng' => $this->siteReportDocument->lng,
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
