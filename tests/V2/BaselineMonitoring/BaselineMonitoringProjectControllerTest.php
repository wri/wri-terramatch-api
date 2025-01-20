<?php

namespace Tests\V2\BaselineMonitoring;

use App\Models\V2\User;
use App\Models\V2\BaselineMonitoring\ProjectMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BaselineMonitoringProjectControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testIndexAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        ProjectMetric::factory()->count(3)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/project-metrics')
            ->assertStatus(200)
            ->assertJsonCount(3 , 'data');


        $this->actingAs($admin)
            ->getJson('/api/v2/project-metrics')
            ->assertStatus(200)
            ->assertJsonCount(3 , 'data');

    }

    public function testViewAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/project-metrics/' . $metrics->uuid)
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $metrics->uuid,
                'tree_count' => $metrics->tree_count,
                'tree_cover' => $metrics->tree_cover,
                'tree_cover_loss' => $metrics->tree_cover_loss
            ]);

        $this->actingAs($admin)
            ->getJson('/api/v2/project-metrics/' . $metrics->uuid)
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $metrics->uuid,
                'tree_count' => $metrics->tree_count,
                'tree_cover' => $metrics->tree_cover,
                'tree_cover_loss' => $metrics->tree_cover_loss
            ]);
    }

    public function testOverviewAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/project-metrics/' . $metrics->uuid . '/overview')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $metrics->uuid,
                'tree_count' => $metrics->tree_count,
                'tree_cover' => $metrics->tree_cover,
                'tree_cover_loss' => $metrics->tree_cover_loss
            ]);

        $this->actingAs($admin)
            ->getJson('/api/v2/project-metrics/' . $metrics->uuid . '/overview')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $metrics->uuid,
                'tree_count' => $metrics->tree_count,
                'tree_cover' => $metrics->tree_cover,
                'tree_cover_loss' => $metrics->tree_cover_loss
            ]);
    }

    public function testUpdateAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create(['tree_cover' => 42]);

        $payload = [
            'tree_cover' => 75.12,
            'tree_count' => 2690.00,
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/project-metrics/' . $metrics->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/v2/project-metrics/' . $metrics->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'tree_cover' => 75.12,
                'tree_count' => 2690.00,
                'field_tree_count' => $metrics->field_tree_count,
                'tree_cover_loss' => $metrics->tree_cover_loss
            ]);
    }

    public function testDeleteAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();
        $uuid =  $metrics->uuid;

        $this->assertEquals(1,ProjectMetric::isUuid($uuid)->count());

        $this->actingAs($user)
            ->deleteJson('/api/v2/project-metrics/' . $metrics->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/project-metrics/' . $metrics->uuid)
            ->assertStatus(202);

        $this->assertEquals(0,ProjectMetric::isUuid($uuid)->count());
    }

    public function testUploadToExistingAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover.png', 10,10);

        $payload = [
            'uuid' => $metrics->uuid,
            'collection' => 'cover',
            'upload_file' => $file
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/project-metrics/upload', $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson('/api/v2/project-metrics/upload', $payload)
            ->assertStatus(201);

    }

    public function testUploadPdfToExistingAction(): void
    {
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->create('report.pdf', 100,'application/pdf');

        $payload = [
            'uuid' => $metrics->uuid,
            'collection' => 'reportPDF',
            'upload_file' => $file
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/project-metrics/upload', $payload)
            ->assertStatus(201);
    }

    public function testUploadToNoneExistingAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover.png', 10,10);

        $payload = [
        'collection' => 'cover',
        'upload_file' => $file
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/project-metrics/upload', $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->postJson('/api/v2/project-metrics/upload', $payload)
            ->assertStatus(201);
    }

    public function testUploadMultipleGalleryToExistingAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        Storage::fake('uploads');
        foreach( ['image-1.jpg','image-2.png','image-3.gif'] as $filename){
            $file = UploadedFile::fake()->image($filename, 10,10);

            $payload = [
                'uuid' => $metrics->uuid,
                'collection' => 'gallery',
                'upload_file' => $file
            ];

            $this->actingAs($user)
                ->postJson('/api/v2/project-metrics/upload', $payload)
                ->assertStatus(403);

            $this->actingAs($admin)
                ->postJson('/api/v2/project-metrics/upload', $payload)
                ->assertStatus(201);
        }

        $record = ProjectMetric::isUuid($metrics->uuid)->first();
        $this->assertCount(3, $record->gallery_files);
    }

    public function testDeleteGalleryItemAction(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        Storage::fake('uploads');
        foreach( ['image-1.jpg','image-2.png','image-3.gif'] as $filename){
            $file = UploadedFile::fake()->image($filename, 10,10);

            $payload = [
                'uuid' => $metrics->uuid,
                'collection' => 'gallery',
                'upload_file' => $file
            ];

            $this->actingAs($admin)
                ->postJson('/api/v2/project-metrics/upload', $payload)
                ->assertStatus(201);
        }

        $record = ProjectMetric::isUuid($metrics->uuid)->first();
        $this->assertCount(3, $record->gallery_files);
        $galleryItem = $record->gallery_files[1];

        $this->actingAs($user)
            ->deleteJson('/api/v2/media/'.$galleryItem['uuid'] .'/gallery', $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/media/'.$galleryItem['uuid'] .'/gallery', $payload)
            ->assertStatus(202);

        $record = ProjectMetric::isUuid($metrics->uuid)->first();
        $this->assertCount(2, $record->gallery_files);
    }

    public function testDownloadSupportFilesAction(): void
    {
        $admin = User::factory()->admin()->create();
        $metrics = ProjectMetric::factory()->create();

        Storage::fake('uploads');
        foreach( ['spreadsheet-test1.csv','spreadsheet-test2.pdf'] as $filename){
            $file = UploadedFile::fake()->create($filename, 100,'application/pdf');

            $payload = [
                'uuid' => $metrics->uuid,
                'collection' => 'support',
                'upload_file' => $file
            ];

            $this->actingAs($admin)
                ->postJson('/api/v2/project-metrics/upload', $payload)
                ->assertStatus(201);
        }
        $file = UploadedFile::fake()->create('report.pdf', 100,'application/pdf');

        $payload = [
            'uuid' => $metrics->uuid,
            'collection' => 'reportPDF',
            'upload_file' => $file
        ];

        $this->actingAs($admin)
            ->postJson('/api/v2/project-metrics/upload', $payload)
            ->assertStatus(201);

       $this->actingAs($admin)
            ->getJson('/api/v2/project-metrics/' . $metrics->uuid . '/download')
            ->assertStatus(200);
    }
}
