<?php

namespace Tests\V2\Leaderships;

use App\Models\V2\Leaderships;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteLeadershipsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_leaderships()
    {
        $user = User::factory()->admin()->create();
        $leaderships = Leaderships::factory()->create([
            'organisation_id' => $user->organisation->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/leaderships/' . $leaderships->uuid)
            ->assertStatus(200);
    }

    public function test_user_cannot_delete_leaderships_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $leaderships = Leaderships::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/leaderships/' . $leaderships->uuid)
            ->assertStatus(403);
    }
}
