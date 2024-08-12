<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundAimsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_action(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create([
            'jobs_created' => 1000,
            'trees_planted' => 90,
        ]);
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $tree = TerrafundTreeSpecies::factory()->create([
            'amount' => 45,
            'treeable_id' => $siteSubmission->id,
            'treeable_type' => TerrafundSiteSubmission::class,
        ]);
        $siteSubmissionTwo = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $tree = TerrafundTreeSpecies::factory()->create([
            'amount' => 45,
            'treeable_id' => $siteSubmissionTwo->id,
            'treeable_type' => TerrafundSiteSubmission::class,
        ]);
        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
            'ft_youth' => 50,
            'ft_women' => 50,
            'ft_men' => 50,
            'ft_total' => 250,
            'pt_youth' => 50,
            'pt_women' => 50,
            'pt_men' => 50,
            'pt_total' => 250,

        ]);
        $programmeSubmissionTwo = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
            'ft_youth' => 50,
            'ft_women' => 50,
            'ft_men' => 50,
            'ft_total' => 250,
            'pt_youth' => 50,
            'pt_women' => 50,
            'pt_men' => 50,
            'pt_total' => 250,

        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/terrafund/programme/' . $terrafundProgramme->id . '/aims');


        $response->assertStatus(200)
        ->assertJsonPath('data.trees_planted_goal', 90)
        ->assertJsonPath('data.trees_planted_count', 90)
        ->assertJsonPath('data.jobs_created_goal', 1000)
        ->assertJsonPath('data.jobs_created_count', 1000);
    }
}
