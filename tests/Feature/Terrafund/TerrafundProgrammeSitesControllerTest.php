<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundProgrammeSitesControllerTest extends TestCase
{
    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $sites = TerrafundSite::factory()->count(10)->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $response = $this->actingAs($user)
            ->getJson('/api/terrafund/programme/' . $terrafundProgramme->id . '/sites?page=1');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'meta' => [
                         'first',
                         'current',
                         'last',
                         'total',
                     ],
                 ])
        ->assertJsonPath('meta.total', 10);
    }

    public function testGetSitesByProjectAction(): void
    {
        $admin = User::factory()->admin()->create();

        $project = TerrafundProgramme::factory()->create();
        TerrafundSite::factory()->count(3)->create(['terrafund_programme_id' => $project->id]);


        $this->actingAs($admin)
            ->getJson('/api/terrafund/programme/' . $project->id .'/all-sites')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
