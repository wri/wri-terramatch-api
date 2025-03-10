<?php

namespace Tests\V2\Projects;

use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminProjectMultiControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
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
