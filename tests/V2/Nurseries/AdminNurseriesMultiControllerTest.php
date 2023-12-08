<?php

namespace Tests\V2\Projects;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminNurseriesMultiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        //         Artisan::call('v2migration:roles --fresh');
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo('framework-terrafund');
        $admin->givePermissionTo('framework-ppc');
        $user = User::factory()->create();
        $nurseries = Nursery::factory()->count(8)->create();
        $firstRecord = $nurseries[4];
        $secondRecord = $nurseries[6];

        $this->actingAs($user)
            ->getJson('/api/v2/admin/nurseries/multi')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/nurseries/multi?ids=' . $firstRecord->uuid . ',' . $secondRecord->uuid)
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
