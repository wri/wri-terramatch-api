<?php

namespace Tests\V2\Organisation;

use App\Mail\OrganisationUserJoinRequested;
use App\Models\Notification;
use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class JoinExistingOrganisationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action(): void
    {
        Mail::fake();
        $organisations = Organisation::factory(['status' => Organisation::STATUS_APPROVED])->count(4)->create();

        $organisation = $organisations[3];
        $applicant = User::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $payload = [
            'organisation_uuid' => $organisation->uuid,
        ];

        $this->actingAs($applicant)
            ->postJson('/api/v2/organisations/join-existing', $payload)
            ->assertSuccessful();

        $this->assertEquals(1, Notification::where('user_id', $owner->id)
            ->where('action', 'user_join_organisation_requested')
            ->count());

        Mail::assertQueued(OrganisationUserJoinRequested::class, function ($mail) use ($owner) {
            return $mail->hasTo($owner->email_address);
        });
    }
}
