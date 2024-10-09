<?php

namespace Tests\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class AdminRejectOrganisationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testInvokeAction(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $organisation = Organisation::factory(['status' => Organisation::STATUS_PENDING])->create();
        $owner = User::factory()->create([
            'email_address' => 'test.account@testing.com',
            'organisation_id' => $organisation->id,
        ]);

        $payload = ['uuid' => $organisation->uuid];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/organisations/reject', $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson('/api/v2/admin/organisations/reject', $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/organisations/reject', $payload)
            ->assertSuccessful();

        $organisation = Organisation::where('uuid', $organisation->uuid)->firstOrFail();
        $this->assertEquals($organisation->status, Organisation::STATUS_REJECTED);
    }
}
