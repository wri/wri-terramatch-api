<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $randomUser = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $submitted = FormSubmission::factory()->create(['organisation_uuid' => $organisation->uuid, 'status' => FormSubmission::STATUS_AWAITING_APPROVAL]);
        $started = FormSubmission::factory()->create(['organisation_uuid' => $organisation->uuid, 'status' => FormSubmission::STATUS_STARTED]);

        $this->actingAs($randomUser)
            ->deleteJson('/api/v2/forms/submissions/' . $started->uuid)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/forms/submissions/' . $submitted->uuid)
            ->assertStatus(406);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/forms/submissions/' . $started->uuid)
            ->assertSuccessful();
    }

    public function test_application_is_deleted_when_last_form_submission_is_deleted()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $application = Application::factory()->create(['organisation_uuid' => $organisation->uuid]);
        $firstSubmission = FormSubmission::factory()->create([
            'organisation_uuid' => $organisation->uuid,
            'application_id' => $application->id,
            'status' => FormSubmission::STATUS_STARTED,
        ]);
        $lastSubmission = FormSubmission::factory()->create([
            'organisation_uuid' => $organisation->uuid,
            'application_id' => $application->id,
            'status' => FormSubmission::STATUS_STARTED,
        ]);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/forms/submissions/' . $firstSubmission->uuid)
            ->assertSuccessful();

        $application->refresh();
        $this->assertNull($application->deleted_at);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/forms/submissions/' . $lastSubmission->uuid)
            ->assertSuccessful();

        $application->refresh();
        $this->assertNotNull($application->deleted_at);
    }

    public function test_a_user_can_delete_their_own_draft_form_submission()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $started = FormSubmission::factory()->create(['organisation_uuid' => $organisation->uuid, 'status' => FormSubmission::STATUS_STARTED]);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/forms/submissions/' . $started->uuid)
            ->assertSuccessful();
    }

    public function test_a_user_cannot_delete_their_own_submitted_form_submission()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $submitted = FormSubmission::factory()->create(['organisation_uuid' => $organisation->uuid, 'status' => FormSubmission::STATUS_AWAITING_APPROVAL]);

        $this->actingAs($owner)
            ->deleteJson('/api/v2/forms/submissions/' . $submitted->uuid)
            ->assertStatus(406);
    }
}
