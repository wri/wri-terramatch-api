<?php

namespace Tests\V2\Leaderships;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreLeadershipsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_leaderships()
    {
        $user = User::factory()->create();

        $payload = [
            'organisation_id' => $user->organisation->uuid,
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
            'nationality' => 'a nationality',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/leaderships', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'position' => 'a position',
                'gender' => 'a gender',
                'age' => 25,
                'nationality' => 'a nationality',
            ]);
    }

    public function test_user_cannot_create_leaderships_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $payload = [
            'organisation_id' => $organisation->uuid,
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
            'nationality' => 'a nationality',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/leaderships', $payload)
            ->assertStatus(403);
    }
}
