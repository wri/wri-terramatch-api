<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundNurserySingleSubmissionCsvExportControllerTest extends TestCase
{
    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $nurserySubmission = TerrafundNurserySubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/nursery/submission/' . $nurserySubmission->id)
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();
        $nurserySubmission = TerrafundNurserySubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/nursery/submission/' . $nurserySubmission->id)
            ->assertStatus(403);
    }
}
