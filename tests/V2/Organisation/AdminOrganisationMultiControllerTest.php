<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminOrganisationMultiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testInvokeAction(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $organisations = Organisation::factory()->count(8)->create();
        $record1 = $organisations[4];
        $record2 = $organisations[6];

        $this->actingAs($user)
            ->getJson('/api/v2/admin/organisations/multi')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/organisations/multi?ids=' . $record1->uuid . ',' . $record2->uuid)
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'uuid' => $record1->uuid,
                'name' => $record1->name,
                ])
            ->assertJsonFragment([
                'uuid' => $record2->uuid,
                'name' => $record2->name,
            ]);
    }
}
