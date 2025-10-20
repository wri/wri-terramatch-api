<?php

namespace Tests\V2\Files;

use App\Models\V2\Organisation;
use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class UploadControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_bulk_upload_validation()
    {
        $service = User::factory()->serviceAccount()->create();
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
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [['uuid' => 'test', 'download_url' => 'test']])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('uuid field is prohibited', $content['errors'][0]['detail']);

        // Payload isn't an array of images
        $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", ['download_url' => 'test'])
            ->assertStatus(422);

        // Payload has incorrect download URL format
        $content = $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [['download_url' => 'test']])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('format is invalid', $content['errors'][0]['detail']);

        // Unreachable URL
        $content = $this->actingAs($service)
            ->postJson("/api/v2/file/upload/site/photos/$site->uuid/bulk_url", [['download_url' => 'https://terramatch.org/foo.jpg']])
            ->assertStatus(422)
            ->json();
        $this->assertStringContainsString('cannot be reached', $content['errors'][0]['detail']);
    }

    public function test_bulk_upload_functionality()
    {
        $service = User::factory()->serviceAccount()->create();
        $site = Site::factory()->create();
        $url = 'http://localhost/images/V2/land-tenures/national-protected-area.png';
        $badMimeUrl = 'http://localhost/images/email_logo.gif';

        // Check a valid upload
        $this->actingAs($service)
            ->postJson(
                "/api/v2/file/upload/site/photos/$site->uuid/bulk_url",
                [['download_url' => $url]]
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
                [['download_url' => $url], ['download_url' => $badMimeUrl]]
            )
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
                [['download_url' => $url], ['download_url' => $url]]
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
                    'download_url' => $url,
                    'title' => 'Test Image',
                    'lat' => 42,
                    'lng' => -50,
                    'is_public' => false,
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
