<?php

namespace Tests\V2;

use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class MediaControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_bulk_delete(): void
    {
        $service = User::factory()->serviceAccount()->create();
        $admin = User::factory()->admin()->create();
        Artisan::call('v2migration:roles');

        $site = Site::factory()->ppc()->create();
        $photo1 = $site->addMedia(UploadedFile::fake()->image('photo1'))->toMediaCollection('photos');
        $photo1->update(['created_by' => $service->id]);
        $photo2 = $site->addMedia(UploadedFile::fake()->image('photo2'))->toMediaCollection('photos');
        $photo2->update(['created_by' => $admin->id]);
        $photo3 = $site->addMedia(UploadedFile::fake()->image('photo3'))->toMediaCollection('photos');
        $photo3->update(['created_by' => $service->id]);

        // No UUIDS is a 404
        $this->actingAs($service)
            ->delete('/api/v2/media')
            ->assertNotFound();

        // Can't delete photo created by admin
        $this->actingAs($service)
            ->delete($this->buildBulkDeleteUrl([$photo1->uuid, $photo2->uuid]))
            ->assertForbidden();
        $this->assertEquals(3, $site->refresh()->getMedia('photos')->count());

        // Only service accounts can use bulk delete
        $this->actingAs($admin)
            ->delete($this->buildBulkDeleteUrl([$photo2->uuid]))
            ->assertForbidden();
        $this->assertEquals(3, $site->refresh()->getMedia('photos')->count());

        // Success case
        $this->actingAs($service)
            ->delete($this->buildBulkDeleteUrl([$photo1->uuid, $photo3->uuid]))
            ->assertSuccessful();
        $this->assertEquals(1, $site->refresh()->getMedia('photos')->count());
    }

    private function buildBulkDeleteUrl($uuids): string
    {
        return '/api/v2/media?uuids[]=' . implode('&uuids[]=', $uuids);
    }
}
