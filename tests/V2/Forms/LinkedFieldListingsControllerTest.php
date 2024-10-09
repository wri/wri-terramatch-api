<?php

namespace Tests\V2\Forms;

use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LinkedFieldListingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/forms/linked-field-listing')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/forms/linked-field-listing')
            ->assertSuccessful();
    }
}
