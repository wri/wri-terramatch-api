<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteFormControllerTest extends TestCase
{
    use RefreshDatabase;
    use RefreshDatabase;
    use WithFaker;

    public function test_admins_can_delete_unpublished_forms()
    {
        $user = User::factory()->admin()->create();
        $form = Form::factory()->unpublished()->create();
        $formSection = FormSection::factory()->create([
            'form_id' => $form->uuid,
            'order' => 1,
            'title' => 'Section 1 title',
            'subtitle' => 'Section 1 subtitle',
            'description' => 'Section 1 description',
        ]);
        $formQuestion = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
        ]);
        $formQuestionOption = FormQuestionOption::factory()->create([
            'form_question_id' => $formQuestion->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/' . $form->uuid)
            ->assertStatus(200);

        $form->refresh();
        $this->assertNotNull($form->deleted_at);

        $formSection->refresh();
        $this->assertNotNull($formSection->deleted_at);

        $formQuestion->refresh();
        $this->assertNotNull($formQuestion->deleted_at);

        $formQuestionOption->refresh();
        $this->assertNotNull($formQuestionOption->deleted_at);
    }

    public function test_non_admins_cannot_delete_published_forms()
    {
        $user = User::factory()->admin()->create();
        $form = Form::factory()->published()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/' . $form->uuid)
            ->assertStatus(422);
    }

    public function test_non_admins_cannot_delete_forms()
    {
        $user = User::factory()->create();
        $form = Form::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/' . $form->uuid)
            ->assertStatus(403);
    }
}
