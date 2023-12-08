<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\User;
use Tests\TestCase;

final class TerrafundSingleNurserySubmissionCsvExportControllerTest extends TestCase
{
    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $nurserySubmission = TerrafundNurserySubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/nursery/' . $nurserySubmission->terrafund_nursery_id . '/submissions')
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();
        $nurserySubmission = TerrafundNurserySubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/nursery/' . $nurserySubmission->terrafund_nursery_id . '/submissions')
            ->assertStatus(403);
    }
}
