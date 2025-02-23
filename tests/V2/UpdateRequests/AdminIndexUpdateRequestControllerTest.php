<?php

namespace Tests\V2\UpdateRequests;

use App\Models\Framework;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIndexUpdateRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        UpdateRequest::truncate();
        $user = User::factory()->create();

        Framework::factory()->create(['slug' => 'terrafund']);
        $tfAdmin = User::factory()->terrafundAdmin()->create();

        Framework::factory()->create(['slug' => 'ppc']);
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        UpdateRequest::factory()->count(3)->create(['framework_key' => 'ppc']);
        UpdateRequest::factory()->count(5)->create(['framework_key' => 'terrafund']);

        $uri = '/api/v2/admin/update-requests';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }
}
