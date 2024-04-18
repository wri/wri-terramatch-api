<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdateRoleAction(): void
    {
        Artisan::call('v2migration:roles');
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->patchJson(
            '/api/users/' . $user->id . '/role',
            [
                'role' => 'terrafund_admin',
            ],
            $this->getHeadersForUser($admin->email_address)
        )
            ->assertStatus(200)
            ->assertJsonFragment([
                'role' => 'terrafund_admin',
            ]);
    }

    public function testUpdateRoleActionRequiresSuperAdmin(): void
    {
        $notAnAdmin = User::factory()->create();
        $user = User::factory()->create();

        $this->patchJson(
            '/api/users/' . $user->id . '/role',
            [
                'role' => 'terrafund_admin',
            ],
            $this->getHeadersForUser($notAnAdmin->email_address)
        )
            ->assertStatus(403);
    }

    public function testUpdateRoleActionCannotUpdateSelf(): void
    {
        $admin = User::factory()->admin()->create();

        $this->patchJson(
            '/api/users/' . $admin->id . '/role',
            [
                'role' => 'terrafund_admin',
            ],
            $this->getHeadersForUser($admin->email_address)
        )
            ->assertStatus(403);
    }
}
