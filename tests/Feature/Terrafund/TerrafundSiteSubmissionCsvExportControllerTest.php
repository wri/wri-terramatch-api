<?php

namespace Tests\Feature\Terrafund;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundSiteSubmissionCsvExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/site/submissions')
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/site/submissions')
            ->assertStatus(403);
    }
}
