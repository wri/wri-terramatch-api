<?php

namespace Tests\V2\Organisation;

use App\Mail\OrganisationUserRejected;
use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class OrganisationRejectUserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $approvedUsers = User::factory()->count(3)->create();
        $requestedUser = User::factory()->create();

        $organisation = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->create();

        $owner = User::factory()->create(['organisation_id' => $organisation->id ]);
        $organisation->partners()->attach($approvedUsers->pluck('id')->toArray(), ['status' => 'approved']);
        $organisation->partners()->attach($requestedUser, ['status' => 'requested']);

        $payload = [
            'organisation_uuid' => $organisation->uuid,
            'user_uuid' => $requestedUser->uuid,
        ];

        $this->assertContains($requestedUser->uuid, $organisation->usersRequested()->pluck('uuid')->toArray());
        $this->assertNotContains($requestedUser->uuid, $organisation->usersApproved()->pluck('uuid')->toArray());

        $this->actingAs($admin)
            ->putJson('/api/v2/organisations/reject-user', $payload)
            ->assertSuccessful();

        $this->assertNotContains($requestedUser->uuid, $organisation->usersApproved()->pluck('uuid')->toArray());
        $this->assertNotContains($requestedUser->uuid, $organisation->usersRequested()->pluck('uuid')->toArray());

        Mail::assertQueued(OrganisationUserRejected::class, function ($mail) use ($requestedUser) {
            return $mail->hasTo($requestedUser->email_address);
        });
    }
}
