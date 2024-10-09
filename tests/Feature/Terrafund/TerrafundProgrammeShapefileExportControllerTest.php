<?php

namespace Tests\Feature\Terrafund;

use App\Models\Organisation;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundProgrammeShapefileExportControllerTest extends TestCase
{
    public function testInvokeAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/' . $terrafundProgramme->id . '/shapefiles')
            ->assertStatus(200);
    }

    public function testUserCanExportOwnAction()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $terrafundProgramme = TerrafundProgramme::factory()->create(['organisation_id' => $organisation->id]);
        $owner->terrafundProgrammes()->attach($terrafundProgramme->id);

        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $uri = '/api/terrafund/export/programme/' . $terrafundProgramme->id . '/shapefiles';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(200);
    }

    public function testInvokeActionRequiresTerrafundAdmin()
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/' . $terrafundProgramme->id . '/shapefiles')
            ->assertStatus(403);
    }
}
