<?php

namespace Tests\V2\ReportingFrameworks;

use App\Models\Framework;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewReportingFrameworkViaAccessCodeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->create();

        $framework = Framework::factory()->create(['name' => 'Testing access code']);

        $uri = '/api/v2/reporting-frameworks/access-code/' . $framework->access_code;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonFragment(['name' => 'Testing access code']);
    }
}
