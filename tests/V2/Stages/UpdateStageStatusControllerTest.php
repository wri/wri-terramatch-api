<?php

namespace Tests\V2\Stages;

use App\Models\User;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateStageStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testAction()
    {
        $user = User::factory()->admin()->create();
        $stage = Stage::factory()->create([
            'status' => 'active',
        ]);

        $payload = [
            'status' => 'inactive',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/funding-programme/stage/' . $stage->uuid . '/status', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'inactive',
            ]);
    }
}
