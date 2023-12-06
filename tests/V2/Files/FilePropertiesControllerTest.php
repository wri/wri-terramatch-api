<?php

namespace Tests\V2\Files;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

final class FilePropertiesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_update_action(): void
    {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

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
            'is_public' => true,
        ];

        $this->actingAs($admin)
            ->putJson('/api/v2/files/' . $uuid, ['no_title' => 'no data'])
            ->assertStatus(422);

        $this->actingAs($admin)
            ->putJson('/api/v2/files/' . $uuid, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'title' => 'Updated Cover Photo Test2',
                'is_public' => true,
                'file_name' => 'cover.png',
            ]);
    }

    public function test_delete_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

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

        $this->actingAs($user)
            ->deleteJson('/api/v2/files/' . $uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/files/' . $uuid)
            ->assertSuccessful();

        $media = Media::where('uuid', $uuid)->first();
        $this->assertNull($media);
    }
}
