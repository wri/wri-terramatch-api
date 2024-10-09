<?php

namespace Tests\Feature\Terrafund;

use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundNurserySubmissionCsvExportControllerTest extends TestCase
{
    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/nursery/submissions')
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/nursery/submissions')
            ->assertStatus(403);
    }
}
