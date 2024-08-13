<?php

namespace Tests\V2\Forms;

use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormOptionsLabelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/forms/option-labels?keys=agroforest,grassland,we-provide-paid-jobs-for-people-older-than-29')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/forms/option-labels?keys=agroforest,grassland,we-provide-paid-jobs-for-people-older-than-29')
            ->assertSuccessful();

        $this->actingAs($user)
            ->getJson('/api/v2/forms/option-labels')
            ->assertStatus(406);

        $this->actingAs($user)
            ->getJson('/api/v2/forms/option-labels?keys=xxaaqqkk')
            ->assertSuccessful();
    }
}
