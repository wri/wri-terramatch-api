<?php

namespace Tests\V2\Sites;

use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSoftDeleteSiteControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_users_cant_soft_delete_sites()
    {
        $user = User::factory()->create();

        $site = Site::factory()->create();

        $this->actingAs($user)
            ->delete('/api/v2/admin/sites/' . $site->uuid)
            ->assertStatus(403);
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admins_can_soft_delete_sites(string $adminType, string $fmKey)
    {
        $user = User::factory()->{$adminType}()->create();

        $site = Site::factory()->{$fmKey}()->create();

        $uri = '/api/v2/admin/sites/' . $site->uuid;

        $this->assertFalse($site->trashed());

        $this->actingAs($user)
            ->delete($uri)
            ->assertSuccessful();

        $site->refresh();

        $this->assertTrue($site->trashed());
    }

    public static function permissionsDataProvider()
    {
        return [
            ['terrafundAdmin', 'terrafund'],
            ['ppcAdmin', 'ppc'],
        ];
    }
}
