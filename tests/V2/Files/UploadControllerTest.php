<?php

namespace Tests\V2\Files;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use function PHPUnit\Framework\assertContains;

final class UploadControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover.png', 10, 10);

        $payload = [
            'title' => 'Cover Photo Test1',
            'upload_file' => $file,
            'lat' => 1.24075,
            'lng' => 12.47423,
            'is_public' => true,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'cover',
                'title' => 'Cover Photo Test1',
                'file_name' => 'cover.png',
                'mime_type' => 'image/png',
                'lat' => 1.24075,
                'lng' => 12.47423,
                'is_public' => true,
            ]);
    }

    public function test_wrong_file_type(): void
    {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover.png', 10, 10);

        $payload = [
            'title' => 'Wrong File type',
            'upload_file' => $file,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/reference/' . $organisation->uuid, $payload)
            ->assertStatus(422);
    }

    public function test_single_file(): void
    {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        Storage::fake('uploads');
        $payload = [
            'title' => 'logo1 type',
            'upload_file' => UploadedFile::fake()->image('logo1.png', 10, 10),
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/logo/' . $organisation->uuid, $payload)
            ->assertSuccessful();

        $payload = [
            'title' => 'logo2 type',
            'upload_file' => UploadedFile::fake()->image('logo2.png', 10, 10),
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/logo/' . $organisation->uuid, $payload)
            ->assertSuccessful();

        $organisation->fresh();
        $this->assertEquals(1, $organisation->getMedia('logo')->count());
        $this->assertEquals('logo2 type', $organisation->getMedia('logo')->first()->name);
    }

    public function test_coordinates_are_accepted_for_just_images()
    {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        Storage::fake('uploads');
        $payload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/logo/' . $organisation->uuid, $payload)
            ->assertStatus(422);

        $payload = [
            'title' => 'logo2 type',
            'upload_file' => UploadedFile::fake()->image('logo2.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/logo/' . $organisation->uuid, $payload)
            ->assertSuccessful();

        $media = $organisation->getMedia('logo')->first();

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'lat' => 1.24075,
            'lng' => 12.47423,
        ]);
    }

    public function test_file_upload_for_a_project_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/project/file/' . $project->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/project/photos/' . $project->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $project->getMedia('file')->first();
        $uploadedPhoto = $project->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $project->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $project->id,
        ]);
    }

    public function test_file_upload_for_a_project_report_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $projectReport = ProjectReport::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/project-report/file/' . $projectReport->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/project-report/photos/' . $projectReport->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $projectReport->getMedia('file')->first();
        $uploadedPhoto = $projectReport->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $projectReport->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $projectReport->id,
        ]);
    }

    public function test_file_upload_for_a_site_is_successful()
    {
        $admin = User::factory()->admin()->create();
        Artisan::call('v2migration:roles');
        $site = Site::factory()->ppc()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/site/file/' . $site->uuid, $documentPayload)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/site/photos/' . $site->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $site->getMedia('file')->first();
        $uploadedPhoto = $site->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $site->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $site->id,
        ]);
    }

    public function test_file_upload_for_a_site_report_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $siteReport = SiteReport::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/site-report/file/' . $siteReport->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/site-report/photos/' . $siteReport->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $siteReport->getMedia('file')->first();
        $uploadedPhoto = $siteReport->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $siteReport->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $siteReport->id,
        ]);
    }

    public function test_file_upload_for_a_nursery_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $nursery = Nursery::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/nursery/file/' . $nursery->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/nursery/photos/' . $nursery->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $nursery->getMedia('file')->first();
        $uploadedPhoto = $nursery->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $nursery->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $nursery->id,
        ]);
    }

    public function test_file_upload_for_a_nursery_report_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $nurseryReport = NurseryReport::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/nursery-report/file/' . $nurseryReport->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/nursery-report/photos/' . $nurseryReport->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $nurseryReport->getMedia('file')->first();
        $uploadedPhoto = $nurseryReport->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $nurseryReport->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $nurseryReport->id,
        ]);
    }

    public function test_file_upload_for_a_project_monitoring_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $projectMonitoring = ProjectMonitoring::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/project-monitoring/file/' . $projectMonitoring->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/project-monitoring/photos/' . $projectMonitoring->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $projectMonitoring->getMedia('file')->first();
        $uploadedPhoto = $projectMonitoring->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $projectMonitoring->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $projectMonitoring->id,
        ]);
    }

    public function test_file_upload_for_a_site_monitoring_is_successful()
    {
        $admin = User::factory()->admin()->create();
        $siteMonitoring = SiteMonitoring::factory()->create();

        Storage::fake('uploads');

        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];
        $photoPayload = [
            'title' => 'photo',
            'upload_file' => UploadedFile::fake()->image('test_photo.png', 10, 10),
            'lat' => 1.24075,
            'lng' => 12.47423,
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/site-monitoring/file/' . $siteMonitoring->uuid, $documentPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/site-monitoring/photos/' . $siteMonitoring->uuid, $photoPayload)
            ->assertSuccessful();

        $uploadedDocument = $siteMonitoring->getMedia('file')->first();
        $uploadedPhoto = $siteMonitoring->getMedia('photos')->first();

        $this->assertDatabaseHas('media', [
            'id' => $uploadedDocument->id,
            'model_id' => $siteMonitoring->id,
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $uploadedPhoto->id,
            'model_id' => $siteMonitoring->id,
        ]);
    }

    public function test_file_upload_sets_media_file_type_successfully()
    {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover.png', 10, 10);

        $mediaPayload = [
            'title' => 'Cover Photo Test1',
            'upload_file' => $file,
            'lat' => 1.24075,
            'lng' => 12.47423,
            'is_public' => true,
        ];
        $documentPayload = [
            'title' => 'general document',
            'upload_file' => UploadedFile::fake()->create('test_file.csv', 10, 'text/csv'),
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, $mediaPayload)
            ->assertSuccessful();
        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/additional/' . $organisation->uuid, $documentPayload)
            ->assertSuccessful();

        $media = $organisation->getMedia('cover')->first();
        $document = $organisation->getMedia('additional')->first();

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'file_type' => 'media',
        ]);
        $this->assertDatabaseHas('media', [
            'id' => $document->id,
            'file_type' => 'documents',
        ]);
    }

    public function test_bulk_upload_validation()
    {
        $service = User::factory()->serviceAccount()->create();
        Artisan::call('v2migration:roles');
        $site = Site::factory()->create();
        $organisation = Organisation::factory()->create();
        // It's not ideal for the testing suite to use a real hosted image, but I haven't found a way to fake a
        // http download URL in phpunit/spatie.
        $url = 'https://new-wri-prod.wri-restoration-marketplace-api.com/images/V2/land-tenures/national-protected-area.png';

        // User doesn't own the site.
        $this->actingAs(User::factory()->create())
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [])
            ->assertForbidden();

        // Service accounts can only upload to sites
        $this->actingAs($service)
            ->postJson("/api/v2/file/upload/organisation/photos/$organisation->uuid/bulk_url", [])
            ->assertForbidden();

        // Only the photos collection is allowed
        $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/pdf/$site->uuid/bulk_url", [])
            ->assertStatus(404);

        // UUID isn't allowed
        $content = $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [["uuid" => "test", "download_url" => "test"]])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('uuid field is prohibited', $content['errors'][0]['detail']);

        // Payload isn't an array of images
        $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", ["download_url" => "test"])
            ->assertStatus(422);

        // Payload has incorrect download URL format
        $content = $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [["download_url" => "test"]])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('format is invalid', $content['errors'][0]['detail']);

        // Unreachable URL
        $content = $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [["download_url" => 'https://terramatch.org/foo.jpg']])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('cannot be reached', $content['errors'][0]['detail']);
    }

    public function test_bulk_upload_functionality()
    {
        $service = User::factory()->serviceAccount()->create();
        Artisan::call('v2migration:roles');
        $site = Site::factory()->create();
        // It's not ideal for the testing suite to use a real hosted image, but I haven't found a way to fake a
        // http download URL in phpunit/spatie.
        $url = 'https://new-wri-prod.wri-restoration-marketplace-api.com/images/V2/land-tenures/national-protected-area.png';
        $badMimeUrl = 'https://www.terramatch.org/images/landing-page-hero-banner.webp';

        // Check a valid upload
        $this->actingAs($service)
            ->postJson(
                "/api/v2/file/upload/site/photos/$site->uuid/bulk_url",
                [["download_url" => $url]]
            )
            ->assertSuccessful();
        $site = $site->refresh();
        $this->assertEquals($site->getMedia('photos')->count(), 1);
        $media = $site->getFirstMedia('photos');
        $this->assertEquals($media->mime_type, 'image/png');
        $this->assertEquals($media->file_name, 'national-protected-area.png');

        // Check that the first file doesn't stick around in an invalid upload
        $site->clearMediaCollection('photos');
        $content = $this->actingAs($service)
            ->postJson(
                "/api/v2/file/upload/site/photos/$site->uuid/bulk_url",
                [["download_url" => $url], ["download_url" => $badMimeUrl]])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('File has a mime type', $content['errors'][0]['detail']);
        $site = $site->refresh();
        $this->assertEquals($site->getMedia('photos')->count(), 0);

        // Check that multiple file upload works
        $site->clearMediaCollection('photos');
        $this->actingAs($service)
            ->postJson(
                "/api/v2/file/upload/site/photos/$site->uuid/bulk_url",
                [["download_url" => $url], ["download_url" => $url]]
            )
            ->assertSuccessful();
        $site = $site->refresh();
        $this->assertEquals($site->getMedia('photos')->count(), 2);

        // Check that optional fields are honored
        $site->clearMediaCollection('photos');
        $this->actingAs($service)
            ->postJson(
                "/api/v2/file/upload/site/photos/$site->uuid/bulk_url",
                [[
                    "download_url" => $url,
                    "title" => "Test Image",
                    "lat" => 42,
                    "lng" => -50,
                    "is_public" => false,
                ]]
            )
            ->assertSuccessful();
        $site = $site->refresh();
        $media = $site->getFirstMedia('photos');
        $this->assertNotNull($media->uuid);
        $this->assertEquals($media->name, 'Test Image');
        $this->assertEquals($media->lat, 42);
        $this->assertEquals($media->lng, -50);
        $this->assertEquals($media->is_public, false);
    }
}
