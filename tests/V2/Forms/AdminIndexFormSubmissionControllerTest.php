<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use Tests\TestCase;

final class AdminIndexFormSubmissionControllerTest extends TestCase
{
    public function test_admins_can_view_form_submission_index(): void
    {
        $user = User::factory()->admin()->create();

        $fundingProgramme = FundingProgramme::factory()->create();
        $stage = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme->uuid,
        ]);
        $form = Form::factory()->create([
            'stage_id' => $stage->uuid,
        ]);
        FormSubmission::factory()->count(5)->create([
            'form_id' => $form->uuid,
        ]);
        $newestForm = FormSubmission::factory()->create([
            'created_at' => now()->addDecade(),
            'form_id' => $form->uuid,
        ]);

        // assert a regular index
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/submissions')
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');

        // assert filtering by funding programme
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/submissions?filter[funding_programme_id]=' . $fundingProgramme->uuid)
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');

        // assert sorting by created at
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/submissions?sort=-created_at')
            ->assertStatus(200)
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.id', $newestForm->id);
    }

    public function test_non_admins_cannot_view_form_index(): void
    {
        $user = User::factory()->create();
        $form = Form::factory()->create();
        FormSubmission::factory()->count(5)->create([
            'form_id' => $form->uuid,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/submissions')
            ->assertStatus(403);
    }
}
