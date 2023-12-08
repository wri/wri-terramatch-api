<?php

namespace Tests\V2\ReportingFrameworks;

use App\Models\Framework;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewReportingFrameworkControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->create();

        $framework = Framework::factory()->create(['name' => 'Testing framework']);

        $uri = '/api/v2/reporting-frameworks/' . $framework->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonFragment(['name' => 'Testing framework']);
    }
}
