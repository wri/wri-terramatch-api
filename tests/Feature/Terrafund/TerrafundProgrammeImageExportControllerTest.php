<?php

namespace Tests\Feature\Terrafund;

use App\Models\Organisation;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\User;
use Tests\TestCase;

final class TerrafundProgrammeImageExportControllerTest extends TestCase
{
    public function testInvokeRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/' . $programme->id .'/images')
            ->assertStatus(403);
    }

    public function testUserCanExportOwnAction()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $terrafundProgramme = TerrafundProgramme::factory()->create(['organisation_id' => $organisation->id]);
        $owner->terrafundProgrammes()->attach($terrafundProgramme->id);

        $uri = '/api/terrafund/export/programme/' . $terrafundProgramme->id . '/images';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(422);
    }

    public function testInvokeRequiresTerrafundProgrammeToHaveFiles()
    {
        $user = User::factory()->terrafundAdmin()->create();
        $programme = TerrafundProgramme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/' . $programme->id .'/images')
            ->assertStatus(422);
    }
}
