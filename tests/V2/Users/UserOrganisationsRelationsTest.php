<?php

namespace Tests\V2\Users;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class UserOrganisationsRelationsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_relations(): void
    {
        $approved = User::factory()->create();
        $rejected = User::factory()->create();
        $requested = User::factory()->create();

        $organisation = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->create();
        $organisation->partners()->attach($approved, ['status' => 'approved']);
        $organisation->partners()->attach($requested, ['status' => 'requested']);
        $organisation->partners()->attach($rejected, ['status' => 'rejected']);
        $uri = '/api/auth/me';

        $this->actingAs($approved)
            ->getJson($uri)
            ->assertSuccessful();
        //            ->assertJsonFragment(['users_status' => 'approved']);

        $this->actingAs($rejected)
            ->getJson($uri)
            ->assertSuccessful();
        //            ->assertJsonFragment(['users_status' => 'rejected']);

        $this->actingAs($requested)
            ->getJson($uri)
            ->assertSuccessful();
        //            ->assertJsonFragment(['users_status' => 'requested']);
    }
}
