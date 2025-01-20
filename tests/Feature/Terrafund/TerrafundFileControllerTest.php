<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Upload;
use App\Models\V2\User;
use Tests\TestCase;

/**
 * Some tests for terrafund files are in the legacy
 * version of this test
 */
final class TerrafundFileControllerTest extends TestCase
{
    public function testCreateActionRequiresAccessToNurserySubmission(): void
    {
        $user = User::factory()->create();
        $nurserySubmission = TerrafundNurserySubmission::factory()->create();
        $file = Upload::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'nursery_submission',
                'fileable_id' => $nurserySubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(403);
    }

    public function testCreateActionRequiresAccessToProgrammeSubmission(): void
    {
        $user = User::factory()->create();
        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create();
        $file = Upload::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'programme_submission',
                'fileable_id' => $programmeSubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(403);
    }
}
