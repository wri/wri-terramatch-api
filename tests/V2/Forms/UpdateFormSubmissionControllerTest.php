<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_update_form_submissions(): void
    {
        $user = User::factory()->create();
        $form = Form::factory()->create();
        $formSection = FormSection::factory()->create([
            'form_id' => $form->uuid,
        ]);
        $formQuestions = FormQuestion::factory()->count(2)->create([
            'form_section_id' => $formSection,
        ]);
        $formSubmission = FormSubmission::factory()->create([
            'form_id' => $form->uuid,
            'user_id' => $user->uuid,
            'organisation_uuid' => $user->organisation->uuid,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/v2/forms/submissions/' . $formSubmission->uuid, [
                'answers' => [
                    $formQuestions[0]->uuid => 'this is the answer',
                    $formQuestions[1]->uuid => 'this is the second answer',
                ],
            ])
            ->assertStatus(200);
    }

    public function test_users_can_update_other_orgs_form_submissions(): void
    {
        $user = User::factory()->create();
        $form = Form::factory()->create();
        $formSection = FormSection::factory()->create([
            'form_id' => $form->uuid,
        ]);
        $formQuestions = FormQuestion::factory()->count(2)->create([
            'form_section_id' => $formSection,
        ]);
        $formSubmission = FormSubmission::factory()->create([
            'form_id' => $form->uuid,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/v2/forms/submissions/' . $formSubmission->uuid, [
                'answers' => [
                    [
                        'question_id' => $formQuestions[0]->id,
                        'value' => 'this is the answer',
                    ],
                    [
                        'question_id' => $formQuestions[1]->id,
                        'value' => 'this is the second answer',
                    ],
                ],
            ])
            ->assertStatus(403);
    }
}
