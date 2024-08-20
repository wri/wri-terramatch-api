<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundProgrammeSubmissionCsvExportControllerTest extends TestCase
{
    public function testSingleProgrammeActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/' . $programme->id .'/submissions')
            ->assertStatus(403);
    }

    public function testAllProgrammesAction(): void
    {
        $user = User::factory()->terrafundAdmin()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/submissions')
            ->assertStatus(200);
    }

    public function testAllProgrammesActionRequiresTerrafundAdmin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/export/programme/submissions')
            ->assertStatus(403);
    }

    public function testMyProgrammeSubmisionsAction()
    {
        $owner = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $owner->terrafundProgrammes()->attach($programme->id);

        $this->actingAs($owner)
            ->getJson('/api/terrafund/export/programme/' . $programme->id .'/submissions')
            ->assertStatus(200);
    }
}
