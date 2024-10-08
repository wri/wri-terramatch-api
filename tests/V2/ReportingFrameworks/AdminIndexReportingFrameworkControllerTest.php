<?php

namespace Tests\V2\ReportingFrameworks;

use App\Models\Framework;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIndexReportingFrameworkControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $count = Framework::count();
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

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
