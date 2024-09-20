<?php

namespace Tests\V2\Forms;

use App\Mail\ApplicationSubmittedConfirmation;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\ProjectPitch;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubmitFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        Mail::fake();
        $user = User::factory()->admin()->create(['locale' => 'en-US']);
        $this->actingAs($user);
        $projectPitch = ProjectPitch::factory()->create([
            'organisation_id' => $user->organisation->uuid,
            'status' => ProjectPitch::STATUS_DRAFT,
        ]);
        $formSubmission = FormSubmission::factory()->create([
            'status' => FormSubmission::STATUS_STARTED,
            'project_pitch_uuid' => $projectPitch->uuid,
            'user_id' => $user->uuid,
        ]);

        $this->actingAs($user)
            ->putJson('/api/v2/forms/submissions/submit/' . $formSubmission->uuid, [])
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => FormSubmission::STATUS_AWAITING_APPROVAL,
            ]);

        $projectPitch->refresh();
        $this->assertEquals(ProjectPitch::STATUS_ACTIVE, $projectPitch->status);

        Mail::assertQueued(ApplicationSubmittedConfirmation::class, function ($mail) use ($user, $formSubmission) {
            return $mail->hasTo($user->email_address) &&
                $mail->body == $formSubmission->form->submission_message;
        });
    }
}
