<?php

namespace Tests\V2\Forms;

use App\Mail\FormSubmissionApproved;
use App\Mail\FormSubmissionRejected;
use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class UpdateFormSubmissionStatusControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_admin_can_update_status_of_form_submission(): void
    {
        Mail::fake();
        $user = User::factory()->admin()->create();
        $applicant = User::factory()->create();
        $formSubmission = FormSubmission::factory()->create([
            'status' => 'awaiting-approval',
            'user_id' => $applicant->uuid,
        ]);

        $feedbackFields = [$this->faker->uuid,$this->faker->uuid,$this->faker->uuid];

        $payload = [
            'status' => 'rejected',
            'feedback' => 'feedback to send',
            'feedback_fields' => $feedbackFields,
        ];

        $this->assertCount(1, $formSubmission->audits); // from when it gets created

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid . '/status', $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'rejected',
                'feedback' => 'feedback to send',
                'feedback_fields' => $feedbackFields,
            ])
            ->assertJsonPath('data.audits.1.event', 'updated')
            ->assertJsonPath('data.audits.1.old_values.status', 'awaiting-approval')
            ->assertJsonPath('data.audits.1.new_values.status', 'rejected');

        $formSubmission->refresh();
        $this->assertCount(2, $formSubmission->audits); // from the update

        Mail::assertQueued(FormSubmissionRejected::class, function ($mail) use ($applicant) {
            return $mail->hasTo($applicant->email_address);
        });
    }

    public function test_user_is_added_to_framework_when_last_stage_submission_approved(): void
    {
        $user = User::factory()->create();
        $formSubmission = FormSubmission::factory()->create([
            'status' => 'awaiting-approval',
        ]);

        $payload = [
            'status' => 'rejected',
            'feedback' => 'feedback to send',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid . '/status', $payload)
            ->assertStatus(403);
    }

    public function test_next_stage_created_on_approved_form_submission()
    {
        Mail::fake();
        $form = Form::where('title', 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)')->first();

        if (empty($form)) {
            Artisan::call('v2-custom-form-update-data');
            Artisan::call('v2-custom-form-prep-phase2');
            Artisan::call('v2-custom-form-rfp-update-data');
            $form = Form::where('title', 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)')->first();
        }

        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();
        $applicant = User::factory()->create(['organisation_id' => $organisation->id]);
        $pitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);

        TreeSpecies::factory()->count($this->faker->numberBetween(0, 5))->create(['speciesable_type' => ProjectPitch::class, 'speciesable_id' => $pitch->id]);

        $formSubmission = FormSubmission::factory()->create([
            'status' => FormSubmission::STATUS_AWAITING_APPROVAL,
            'organisation_uuid' => $organisation->uuid,
            'user_id' => $applicant->uuid,
            'form_id' => $form->uuid,
            'stage_uuid' => $form->stage_id,
        ]);

        $payload = [
            'status' => FormSubmission::STATUS_APPROVED,
        ];

        $this->actingAs($admin)
            ->patchJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid . '/status', $payload)
            ->assertStatus(200);

        Mail::assertQueued(FormSubmissionApproved::class, function ($mail) use ($applicant) {
            return $mail->hasTo($applicant->email_address);
        });
    }
}
