<?php

namespace Tests\V2\Nurseries;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSoftDeleteNurseryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_users_cant_soft_delete_nurseries()
    {
        $user = User::factory()->create();

        $nursery = Nursery::factory()->create();

        $this->actingAs($user)
            ->delete('/api/v2/admin/nurseries/' . $nursery->uuid)
            ->assertStatus(403);
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admins_can_soft_delete_nurseries(string $adminType, string $fmKey)
    {
        $user = User::factory()->{$adminType}()->create();

        $nursery = Nursery::factory()->{$fmKey}()->create();

        $uri = '/api/v2/admin/nurseries/' . $nursery->uuid;

        $this->assertFalse($nursery->trashed());

        $this->actingAs($user)
            ->delete($uri)
            ->assertSuccessful();

        $nursery->refresh();

        $this->assertTrue($nursery->trashed());
    }

    public static function permissionsDataProvider()
    {
        return [
            ['terrafundAdmin', 'terrafund'],
            ['ppcAdmin', 'ppc'],
        ];
    }
}
