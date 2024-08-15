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

class UpdateFormControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_admins_can_update_forms()
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
        $formQuestionOptionToDelete = FormQuestionOption::factory()->create([
            'form_question_id' => $formQuestion->id,
        ]);
        $formQuestionOptionToKeep = FormQuestionOption::factory()->create([
            'form_question_id' => $formQuestion->id,
        ]);

        $payload = [
            'title' => 'Form new name',
            'documentation_label' => 'Documentation label',
            'deadline_at' => '2024-07-08 12:00:00',
            'documentation' => null,
            'form_sections' => [
                [
                    'uuid' => $formSection->uuid,
                    'title' => 'New form section 1',
                    'subtitle' => 'New form section 1 subtitle',
                    'order' => 10,
                    'form_questions' => [
                        [
                            'uuid' => $formQuestion->uuid,
                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                            'label' => 'New question 1 label',
                            'order' => 20,
                            'input_type' => 'text',
                            'form_question_options' => [
                                [
                                    'uuid' => $formQuestionOptionToKeep->uuid,
                                    'slug' => 'keep-the-option',
                                    'label' => 'keep the option',
                                    'order' => 1,
                                ],
                            ],
                            'child_form_questions' => [
                                [
                                    'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                    'label' => 'Child Question 1 label',
                                    'placeholder' => 'Child Question 1 placeholder',
                                    'description' => 'Child Question 1 description',
                                    'input_type' => 'text',
                                    'multichoice' => false,
                                    'validation' => [
                                        'required' => true,
                                    ],
                                    'order' => 1,
                                ], [
                                    'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                    'label' => 'Child Question 2 label',
                                    'placeholder' => 'Child Question 2 placeholder',
                                    'description' => 'Child Question 2 description',
                                    'input_type' => 'text',
                                    'multichoice' => false,
                                    'validation' => [
                                        'required' => true,
                                    ],
                                    'order' => 1,
                                ],
                            ],
                        ],
                        [
                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                            'label' => 'Question 2 label',
                            'placeholder' => 'Question 2 placeholder',
                            'description' => 'Question 2 description',
                            'input_type' => 'select',
                            'form_question_options' => [
                                [
                                    'slug' => 'option-1',
                                    'label' => 'Option 1',
                                    'order' => 1,
                                ],
                                [
                                    'slug' => 'option-2',
                                    'label' => 'Option 2',
                                    'order' => 1,
                                ],
                            ],
                            'multichoice' => true,
                            'validation' => [
                                'required' => true,
                            ],
                            'order' => 1,
                        ],
                    ],
                ],
                [
                    'order' => 1,
                    'title' => 'New form section',
                    'subtitle' => 'New form section subtitle',
                    'description' => 'New form section description',
                    'form_questions' => [
                        [
                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                            'label' => 'Question 1 label',
                            'placeholder' => 'Question 1 placeholder',
                            'description' => 'Question 1 description',
                            'input_type' => 'text',
                            'validation' => [
                                'required' => true,
                            ],
                            'order' => 1,
                            'child_form_questions' => [
                                [
                                    'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                    'label' => 'Child Question 1 label',
                                    'placeholder' => 'Child Question 1 placeholder',
                                    'description' => 'Child Question 1 description',
                                    'input_type' => 'text',
                                    'validation' => [
                                        'required' => true,
                                    ],
                                    'order' => 1,
                                ], [
                                    'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                    'label' => 'Child Question 2 label',
                                    'placeholder' => 'Child Question 2 placeholder',
                                    'description' => 'Child Question 2 description',
                                    'input_type' => 'text',
                                    'validation' => [
                                        'required' => true,
                                    ],
                                    'order' => 1,
                                ],
                            ],
                        ],
                        [
                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                            'label' => 'Question 2 label',
                            'placeholder' => 'Question 2 placeholder',
                            'description' => 'Question 2 description',
                            'input_type' => 'select',
                            'form_question_options' => [
                                [
                                    'slug' => 'option-1',
                                    'label' => 'Option 1',
                                    'order' => 1,
                                ],
                                [
                                    'slug' => 'option-2',
                                    'label' => 'Option 2',
                                    'order' => 1,
                                ],
                            ],
                            'multichoice' => true,
                            'validation' => [
                                'required' => true,
                            ],
                            'order' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/' . $form->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Form new name',
                'documentation_label' => 'Documentation label',
                'documentation' => null,
                'deadline_at' => '2024-07-08T17:00:00.000000Z',
            ]);

        $formQuestionOptionToKeep->refresh();
        $this->assertNull($formQuestionOptionToKeep->deleted_at);
    }

    public function test_admins_can_update_forms_with_conditional_fields()
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

        $formQuestion = FormQuestion::factory()->conditionalField()->create([
            'form_section_id' => $formSection->id,
        ]);

        $childA = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'order' => 1,
            'parent_id' => data_get($formQuestion, 'uuid'),
            'show_on_parent_condition' => true,
        ]);

        $childB = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'order' => 2,
            'parent_id' => data_get($formQuestion, 'uuid'),
            'show_on_parent_condition' => false,
        ]);

        $childC = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'order' => 3,
            'parent_id' => data_get($formQuestion, 'uuid'),
            'show_on_parent_condition' => false,
        ]);

        $payload = [
            'title' => 'Form new name',
            'documentation_label' => 'Documentation label',
            'deadline_at' => '2024-07-08 12:00:00',
            'documentation' => null,
            'form_sections' => [
                [
                    'uuid' => $formSection->uuid,
                    'title' => 'New form section 1',
                    'subtitle' => 'New form section 1 subtitle',
                    'order' => 11,
                    'form_questions' => [
                        [
                            'uuid' => $formQuestion->uuid,
                            'label' => 'New question 1 label',
                            'input_type' => 'text',
                            'order' => 30,
                            'child_form_questions' => [
                                [
                                    'uuid' => $childA->uuid,
                                    'show_on_parent_condition' => false,
                                    'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                    'label' => 'Child Question 1 label',
                                    'placeholder' => 'Child Question 1 placeholder',
                                    'description' => 'Child Question 1 description',
                                    'input_type' => 'text',
                                    'multichoice' => false,
                                    'validation' => [
                                        'required' => true,
                                    ],
                                    'order' => 6,
                                ],
                                [
                                    'uuid' => $childB->uuid,
                                    'show_on_parent_condition' => false,
                                    'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                    'label' => 'Child Question 1 label',
                                    'placeholder' => 'Child Question 1 placeholder',
                                    'description' => 'Child Question 1 description',
                                    'input_type' => 'text',
                                    'multichoice' => false,
                                    'validation' => [
                                        'required' => true,
                                    ],
                                    'order' => 9,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/' . $form->uuid, $payload)
            ->assertStatus(200);

        $childA->refresh();
        $this->assertNull($childA->deleted_at);
        $this->assertEquals(6, $childA->order);
        $this->assertEquals(false, $childA->show_on_parent_condition);

        $childB->refresh();
        $this->assertNull($childB->deleted_at);
        $this->assertEquals(9, $childB->order);
        $this->assertEquals(false, $childB->show_on_parent_condition);
    }

    public function test_conditional_fields_can_contain_table()
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

        $conditionalQuestion = FormQuestion::factory()->conditionalField()->create([
            'form_section_id' => $formSection->id,
        ]);

        $tableHeader = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'input_type' => 'tableInput',
            'order' => 1,
            'parent_id' => data_get($conditionalQuestion, 'uuid'),
            'show_on_parent_condition' => true,
        ]);

        $childA = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'order' => 1,
            'parent_id' => data_get($tableHeader, 'uuid'),
            'show_on_parent_condition' => false,
        ]);

        $childB = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'order' => 2,
            'parent_id' => data_get($tableHeader, 'uuid'),
            'show_on_parent_condition' => false,
        ]);

        $childC = FormQuestion::factory()->create([
            'form_section_id' => $formSection->id,
            'order' => 3,
            'parent_id' => data_get($tableHeader, 'uuid'),
            'show_on_parent_condition' => false,
        ]);

        $payload = [
            'title' => 'Form new name',
            'documentation_label' => 'Documentation label',
            'deadline_at' => '2024-07-08 12:00:00',
            'documentation' => null,
            'form_sections' => [
                [
                    'uuid' => $formSection->uuid,
                    'title' => 'New form section 1',
                    'subtitle' => 'New form section 1 subtitle',
                    'input_type' => 'text',
                    'order' => 13,
                    'form_questions' => [
                        [
                            'uuid' => $conditionalQuestion->uuid,
                            'label' => 'New question 1 label',
                            'input_type' => 'text',
                            'order' => 40,
                            'child_form_questions' => [
                                [
                                    'uuid' => $tableHeader->uuid,
                                    'show_on_parent_condition' => true,
                                    'label' => 'Table Header1',
                                    'input_type' => 'tableInput',
                                    'table_headers' => [['label' => 'Heading1'], ['label' => 'Heading2']],
                                    'order' => 6,
                                    'child_form_questions' => [
                                        [
                                            'uuid' => $childA->uuid,
                                            'show_on_parent_condition' => false,
                                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                            'label' => 'Child Question 1 label',
                                            'placeholder' => 'Child Question 1 placeholder',
                                            'description' => 'Child Question 1 description',
                                            'input_type' => 'text',
                                            'multichoice' => false,
                                            'validation' => [
                                                'required' => true,
                                            ],
                                            'order' => 6,
                                        ],
                                        [
                                            'uuid' => $childB->uuid,
                                            'show_on_parent_condition' => false,
                                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                                            'label' => 'Child Question 1 label',
                                            'placeholder' => 'Child Question 2 placeholder',
                                            'description' => 'Child Question 2 description',
                                            'input_type' => 'text',
                                            'multichoice' => false,
                                            'validation' => [
                                                'required' => true,
                                            ],
                                            'order' => 9,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/' . $form->uuid, $payload)
            ->assertStatus(200);
    }

    public function test_non_admins_cannot_update_forms()
    {
        $user = User::factory()->create();
        $form = Form::factory()->create();

        $payload = [
            'title' => 'form',
            'subtitle' => 'form subtitle',
            'description' => 'description',
            'submission_message' => 'submission message',
            'documentation' => 'some documentation',
            'duration' => '5 years',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/' . $form->uuid, $payload)
            ->assertStatus(403);
    }
}
