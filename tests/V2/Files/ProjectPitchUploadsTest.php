<?php

namespace Tests\V2\Files;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProjectPitchUploadsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_permissions(): void
    {
        $randomUser = User::factory()->create();
        $admin = User::factory()->admin()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('test-file.png', 10, 10);

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $projectPitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);
        $monitoringUser = User::factory()->create();
        $organisation->partners()->attach($monitoringUser, ['status' => 'approved']);
        $uri = '/api/v2/file/upload/project-pitch/additional/' . $projectPitch->uuid;

        $payload = [
            'title' => 'test file1',
            'upload_file' => $file,
        ];

        $this->actingAs($randomUser)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'test file1',
                'file_name' => 'test-file.png',
                'mime_type' => 'image/png',
            ]);

        $file = UploadedFile::fake()->image('test-file2.png', 10, 10);
        $payload = [
            'title' => 'test file2',
            'upload_file' => $file,
        ];

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'test file2',
                'file_name' => 'test-file2.png',
                'mime_type' => 'image/png',
            ]);

        $file = UploadedFile::fake()->image('test-file3.png', 10, 10);
        $payload = [
            'title' => 'test file3',
            'upload_file' => $file,
        ];

        $this->actingAs($monitoringUser)
            ->postJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'test file3',
                'file_name' => 'test-file3.png',
                'mime_type' => 'image/png',
            ]);

        $this->actingAs($owner)
            ->getJson('/api/v2/project-pitches/' . $projectPitch->uuid)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data.additional')
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'test file2',
                'file_name' => 'test-file2.png',
                'mime_type' => 'image/png',
            ])
            ->assertJsonFragment([
                'collection_name' => 'additional',
                'title' => 'test file3',
                'file_name' => 'test-file3.png',
                'mime_type' => 'image/png',
            ]);
    }
}
