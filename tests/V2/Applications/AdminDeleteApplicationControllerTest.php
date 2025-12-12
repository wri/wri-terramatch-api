<?php

namespace Tests\V2\Applications;

use App\Models\V2\Forms\Application;
use App\Models\V2\FundingProgramme;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeleteApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $application = Application::factory()->create(
            ['funding_programme_uuid' => $fundingProgramme->uuid]
        );

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->deleteJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertStatus(404);
    }
}
