<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundDisturbance;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundDisturbanceControllerTest extends TestCase
{
    public function testCreateActionRequiresAccessToSiteSubmission(): void
    {
        $user = User::factory()->create();
        $siteSubmission = TerrafundSiteSubmission::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/terrafund/disturbance', [
                'disturbanceable_type' => 'site_submission',
                'disturbanceable_id' => $siteSubmission->id,
                'type' => 'manmade',
                'description' => 'description of the disturbance',
            ])
            ->assertStatus(403);
    }

    public function testDeleteActionRequiresAccessToSiteSubmission(): void
    {
        $user = User::factory()->create();
        $disturbance = TerrafundDisturbance::factory()->create([
             'disturbanceable_type' => TerrafundSiteSubmission::class,
             'disturbanceable_id' => TerrafundSiteSubmission::factory()->create(),
         ]);

        $this->actingAs($user)
             ->deleteJson('/api/terrafund/disturbance/' . $disturbance->id)
             ->assertStatus(403);
    }

    public function testUpdateActionRequiresAccessToSiteSubmission(): void
    {
        $user = User::factory()->create();
        $disturbance = TerrafundDisturbance::factory()->create([
             'disturbanceable_type' => TerrafundSiteSubmission::class,
             'disturbanceable_id' => TerrafundSiteSubmission::factory()->create(),
         ]);

        $this->actingAs($user)
             ->patchJson('/api/terrafund/disturbance/' . $disturbance->id, [
                'description' => 'new description',
                'type' => 'climatic',
             ])
             ->assertStatus(403);
    }
}
