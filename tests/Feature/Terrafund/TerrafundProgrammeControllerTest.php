<?php

namespace Tests\Feature\Terrafund;

use App\Models\Organisation;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundProgrammeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_for_org_action(): void
    {
        $user = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        TerrafundProgramme::factory()->count(5)->create([
            'organisation_id' => $organisation->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/api/organisations/'.$organisation->id.'/terrafund/programmes');

        $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
    }

    public function test_read_for_org_action_must_be_admin(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        TerrafundProgramme::factory()->count(5)->create([
            'organisation_id' => $organisation->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/api/organisations/'.$organisation->id.'/terrafund/programmes');

        $response->assertStatus(403);
    }
}
