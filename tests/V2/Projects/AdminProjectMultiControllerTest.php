<?php

namespace Tests\V2\Projects;

use App\Models\User;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminProjectMultiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        // Artisan::call('v2migration:roles');
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo('framework-terrafund');
        $admin->givePermissionTo('framework-ppc');
        $user = User::factory()->create();
        $projects = Project::factory()->count(8)->create();
        $firstRecord = $projects[4];
        $secondRecord = $projects[6];

        $this->actingAs($user)
            ->getJson('/api/v2/admin/projects/multi')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/projects/multi?ids=' . $firstRecord->uuid . ',' . $secondRecord->uuid)
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
