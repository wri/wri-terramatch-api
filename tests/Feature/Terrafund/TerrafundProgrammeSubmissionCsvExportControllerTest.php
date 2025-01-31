<?php

namespace Tests\Feature\Terrafund;

use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundProgrammeSubmissionCsvExportControllerTest extends TestCase
{
    public function testAllProgrammesAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/submissions')
            ->assertStatus(200);
    }

    public function testAllProgrammesActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/submissions')
            ->assertStatus(403);
    }
}
