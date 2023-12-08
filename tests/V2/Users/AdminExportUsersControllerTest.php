<?php

namespace Tests\V2\Users;

use App\Models\User;
use App\Models\V2\User as V2User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminExportUsersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $users = V2User::factory()->count(10)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/users/export')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/users/export')
            ->assertSuccessful();
    }
}
