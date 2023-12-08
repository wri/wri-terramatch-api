<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\User;
use Tests\TestCase;

final class TerrafundSingleSiteSubmissionCsvExportControllerTest extends TestCase
{
    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $siteSubmission = TerrafundSiteSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/site/' . $siteSubmission->terrafund_site_id . '/submissions')
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();
        $siteSubmission = TerrafundSiteSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/site/' . $siteSubmission->terrafund_site_id . '/submissions')
            ->assertStatus(403);
    }
}
