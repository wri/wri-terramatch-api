<?php

namespace Tests\V2\Projects;

use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminSoftDeleteProjectControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_users_cant_soft_delete_projects(string $permission, string $fmKey)
    {
        $user = User::factory()->create();

        $project = Project::factory()->create(['framework_key' => $fmKey]);

        $uri = '/api/v2/admin/projects/' . $project->uuid;


        $this->actingAs($user)
            ->delete($uri)
            ->assertStatus(403);
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admins_can_soft_delete_projects(string $permission, string $fmKey)
    {
        Artisan::call('v2migration:roles');

        $user = User::factory()->admin()->create();
        $user->givePermissionTo($permission);

        $project = Project::factory()->create(['framework_key' => $fmKey]);

        $uri = '/api/v2/admin/projects/' . $project->uuid;

        $this->assertFalse($project->trashed());

        $this->actingAs($user)
            ->delete($uri)
            ->assertSuccessful();

        $project->refresh();

        $this->assertTrue($project->trashed());
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
