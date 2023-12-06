<?php

namespace Tests\V2\Files;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class OrganisationUploadsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_permissions(): void
    {
        $randomUser = User::factory()->create();
        $admin = User::factory()->admin()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover photo.png', 10, 10);

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation ->id]);
        $monitoringUser = User::factory()->create();
        $organisation->partners()->attach($monitoringUser, ['status' => 'approved']);

        $payload = [
            'title' => 'Cover Photo Test1',
            'upload_file' => $file,
        ];

        $this->actingAs($randomUser)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'cover',
                'title' => 'Cover Photo Test1',
                'file_name' => 'cover-photo.png',
                'mime_type' => 'image/png',
            ]);

        $file = UploadedFile::fake()->image('test image.png', 10, 10);
        $payload = [
            'title' => 'Gallery Photo Test2',
            'upload_file' => $file,
        ];

        $this->actingAs($owner)
            ->postJson('/api/v2/file/upload/organisation/additional/' . $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'Gallery Photo Test2',
                'file_name' => 'test-image.png',
                'mime_type' => 'image/png',
            ]);

        $file = UploadedFile::fake()->image('small gallery image.png', 10, 10);
        $payload = [
            'title' => 'Gallery Photo Test3',
            'upload_file' => $file,
        ];

        $this->actingAs($monitoringUser)
            ->postJson('/api/v2/file/upload/organisation/additional/' . $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'Gallery Photo Test3',
                'file_name' => 'small-gallery-image.png',
                'mime_type' => 'image/png',
            ]);

        $this->actingAs($owner)
            ->getJson('/api/v2/organisations/' . $organisation->uuid)
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.additional')
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'Gallery Photo Test2',
                'file_name' => 'test-image.png',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'Gallery Photo Test3',
                'file_name' => 'small-gallery-image.png',
                'mime_type' => 'image/png',
            ]);
    }

    public function test_update_action(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation]);

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover.png', 10, 10);

        $payload = [
            'title' => 'Cover Photo Test1',
            'upload_file' => $file,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'title' => 'Cover Photo Test1',
                'file_name' => 'cover.png',
            ]);

        $uuid = json_decode($response->getContent())->data->uuid;

        $payload = [
            'title' => 'Updated Cover Photo Test2',
        ];

        $this->actingAs($admin)
            ->putJson('/api/v2/files/' . $uuid, ['no_title' => 'no data'])
            ->assertStatus(422);

        $this->actingAs($user)
            ->putJson('/api/v2/files/' . $uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/v2/files/' . $uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'title' => 'Updated Cover Photo Test2',
                'file_name' => 'cover.png',
            ]);

        $payload = [
            'title' => 'Owner Updated Cover Photo Test2',
        ];

        $this->actingAs($owner)
            ->putJson('/api/v2/files/' . $uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'title' => 'Owner Updated Cover Photo Test2',
                'file_name' => 'cover.png',
            ]);
    }
}
