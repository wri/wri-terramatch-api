<?php

namespace Tests\V2\Users;

use App\Models\User;
use App\Models\V2\User as V2User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminUserMultiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $users = V2User::factory()->count(20)->create();
        $record1 = $users[8];
        $record2 = $users[12];

        $this->actingAs($user)
            ->getJson('/api/v2/admin/users')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users/multi')
            ->assertStatus(406);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users/multi?ids=this,is,not,valid')
            ->assertStatus(404);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users/multi?ids=' . $record1->uuid . ',' . $record2->uuid)
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'uuid' => $record1->uuid,
                'first_name' => $record1->first_name,
            ])
            ->assertJsonFragment([
                'uuid' => $record2->uuid,
                'first_name' => $record2->first_name,
            ]);
    }
}
