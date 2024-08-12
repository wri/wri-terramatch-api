<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class OrganisationListingControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_listing_action(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        Organisation::factory(['status' => Organisation::STATUS_APPROVED, 'name' => 'xxxxx', ])->count(8)->create();
        Organisation::factory(['status' => Organisation::STATUS_APPROVED, 'name' => 'TestOrg1'])->create();
        Organisation::factory(['status' => Organisation::STATUS_APPROVED, 'name' => 'Org2Testing'])->create();
        Organisation::factory(['status' => Organisation::STATUS_APPROVED, 'name' => 'Testorg3'])->create();
        Organisation::factory(['status' => Organisation::STATUS_APPROVED, 'name' => '4orgTest'])->create();
        Organisation::factory(['status' => Organisation::STATUS_DRAFT, 'name' => 'xxxxx', ])->count(6)->create();
        Organisation::factory(['status' => Organisation::STATUS_DRAFT, 'name' => 'testOrg5'])->create();
        Organisation::factory(['status' => Organisation::STATUS_DRAFT, 'name' => 'testOrg6'])->create();
        Organisation::factory(['status' => Organisation::STATUS_REJECTED])->count(9)->create();
        Organisation::factory(['status' => Organisation::STATUS_REJECTED, 'name' => 'testOrg8'])->create();
        Organisation::factory(['status' => Organisation::STATUS_REJECTED, 'name' => 'testOrg9'])->create();

        $this->getJson('/api/v2/organisations/listing')
            ->assertStatus(403);

        $this->actingAs($user)
            ->getJson('/api/v2/organisations/listing')
            ->assertStatus(200);
        //            ->assertJsonCount(14, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/organisations/listing')
            ->assertStatus(200);
        //            ->assertJsonCount(14, 'data');

        $this->actingAs($user)
            ->getJson('/api/v2/organisations/listing?search=test')
            ->assertStatus(200);
        //            ->assertJsonCount(4, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/organisations/listing?search=testorg')
            ->assertStatus(200);
        //            ->assertJsonCount(2, 'data');
    }
}
