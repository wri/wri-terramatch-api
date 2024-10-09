<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\Submission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;

class ProgrammeSubmissionAdminCsvExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_programme_requires_admin()
    {
        $user = User::factory()->create();
        $programme = Programme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/ppc/export/programme/submissions')
            ->assertStatus(403);
    }

    public function test_admin_invoke_action()
    {
        $admin = User::factory()->admin()->create();

        $programmes = Programme::factory()->count(5)->create();
        foreach ($programmes as $programme) {
            $submissions = Submission::factory()->count(2)->create(['programme_id' => $programme->id]);
            foreach ($submissions as $submission) {
                foreach (['programme-submission', 'document_files'] as $collectionName) {
                    for ($i = 0; $i < 2; $i++) {
                        $uploadResponse = $this->actingAs($admin)
                            ->post('/api/uploads', [
                            'upload' => $this->fakeImage(),
                        ]);

                        $this->actingAs($admin)
                            ->postJson('/api/document_files/file', [
                            'document_fileable_id' => $submission->id,
                            'document_fileable_type' => 'programme_submission',
                            'upload' => $uploadResponse->json('data.id'),
                            'title' => 'test image',
                            'collection' => $collectionName,
                            'is_public' => false,
                        ])
                            ->assertStatus(201);
                    }
                }
            }
        }

        $this->actingAs($admin)
            ->getJson('/api/ppc/export/programme/submissions')
            ->assertStatus(200);
    }

    private function fakeImage()
    {
        return new File('image.png', fopen(resource_path() . '/seeds/image.png', 'r'));
    }
}
