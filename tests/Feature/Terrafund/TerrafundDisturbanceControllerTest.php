<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundDisturbance;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundDisturbanceControllerTest extends TestCase
{
    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/disturbance', [
                'disturbanceable_type' => 'site_submission',
                'disturbanceable_id' => $siteSubmission->id,
                'type' => 'manmade',
                'description' => 'description of the disturbance',
            ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'disturbanceable_type' => TerrafundSiteSubmission::class,
                'disturbanceable_id' => $siteSubmission->id,
                'type' => 'manmade',
                'description' => 'description of the disturbance',
            ]);
    }

    public function testCreateActionRequiresValidType(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/disturbance', [
                'disturbanceable_type' => 'site_submission',
                'disturbanceable_id' => $siteSubmission->id,
                'type' => 'notatype',
                'description' => 'a description',
            ])
            ->assertStatus(422);
    }

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

    public function testDeleteAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $disturbance = TerrafundDisturbance::factory()->create([
            'disturbanceable_type' => TerrafundSiteSubmission::class,
            'disturbanceable_id' => $siteSubmission->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/terrafund/disturbance/' . $disturbance->id)
            ->assertStatus(200);
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

    public function testUpdateAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $disturbance = TerrafundDisturbance::factory()->create([
            'disturbanceable_type' => TerrafundSiteSubmission::class,
            'disturbanceable_id' => $siteSubmission->id,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/terrafund/disturbance/' . $disturbance->id, [
                'description' => 'new description',
                'type' => 'climatic',
            ])
            ->assertStatus(200);
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
