<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundSiteSingleSubmissionCsvExportControllerTest extends TestCase
{
    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $siteSubmission = TerrafundSiteSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/site/submission/' . $siteSubmission->id)
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();
        $siteSubmission = TerrafundSiteSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/site/submission/' . $siteSubmission->id)
            ->assertStatus(403);
    }
}
