<?php

namespace Tests\V2\Sites;

use App\Models\User;
use App\Models\V2\Sites\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminSitesMultiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles --fresh');
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo('framework-terrafund');
        $admin->givePermissionTo('framework-ppc');
        $user = User::factory()->create();
        $sites = Site::factory()->count(8)->create();
        $firstRecord = $sites[4];
        $secondRecord = $sites[6];

        $this->actingAs($user)
            ->getJson('/api/v2/admin/sites/multi')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/sites/multi?ids=' . $firstRecord->uuid . ',' . $secondRecord->uuid)
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'uuid' => $firstRecord->uuid,
                'name' => $firstRecord->name,
                ])
            ->assertJsonFragment([
                'uuid' => $secondRecord->uuid,
                'name' => $secondRecord->name,
            ]);
    }
}
