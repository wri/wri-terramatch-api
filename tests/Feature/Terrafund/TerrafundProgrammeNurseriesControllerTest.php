<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\User;
use Tests\TestCase;

final class TerrafundProgrammeNurseriesControllerTest extends TestCase
{
    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nurseries = TerrafundNursery::factory()->count(10)->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $response = $this->actingAs($user)
            ->getJson('/api/terrafund/programme/' . $terrafundProgramme->id . '/nurseries?page=1');
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
}
