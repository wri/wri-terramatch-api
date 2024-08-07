<?php

namespace Tests\V2\ReportingFrameworks;

use App\Models\Framework;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminIndexReportingFrameworkControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles');
        $count = Framework::count();
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo(['framework-ppc', 'framework-terrafund']);

        $framework = Framework::factory()->count(2)->create();

        $uri = '/api/v2/admin/reporting-frameworks';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount($count + 2, 'data');
    }
}
