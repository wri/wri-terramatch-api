<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class StoreFormControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_admins_can_create_forms(): void
    {
        $user = User::factory()->admin()->create();
        $stage = Stage::factory()->create();
        $optionList = FormOptionList::factory()->create();
        $options = FormOptionListOption::factory(3)->create([
            'form_option_list_id' => $optionList,
        ]);

        $payload = [
            'title' => 'form',
            'subtitle' => 'form subtitle',
            'description' => 'description',
            'submission_message' => 'submission message',
            'documentation' => 'some documentation',
            'documentation_label' => 'Documentation label',
            'duration' => '5 years',
            'deadline_at' => '2024-07-08 12:00:00',
            'stage_id' => $stage->id,
            'published' => true,
            'form_sections' => [
                [
                    'order' => 1,
                    'title' => 'Form section 1',
                    'subtitle' => 'Form section 1 subtitle',
                    'description' => 'Form section 1 description',
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
                        [
                            'linked_field_key' => $this->faker->randomElement(array_keys(config('wri.linked-fields.models.organisation.fields'))),
                            'label' => 'Question 3 label',
                            'placeholder' => 'Question 3 placeholder',
                            'description' => 'Question 3 description',
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
                                [
                                    'slug' => $options[0]->slug,
                                    'label' => $options[0]->label,
                                    'order' => 1,
                                    'form_option_list_option_id' => $options[0]->uuid,
                                ],
                                [
                                    'slug' => $options[1]->slug,
                                    'label' => $options[1]->label,
                                    'order' => 2,
                                    'form_option_list_option_id' => $options[1]->uuid,
                                ],
                                [
                                    'slug' => $options[2]->slug,
                                    'label' => $options[2]->label,
                                    'order' => 3,
                                    'form_option_list_option_id' => $options[2]->uuid,
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
            ->postJson('/api/v2/admin/forms', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'form',
                'subtitle' => 'form subtitle',
                'description' => 'description',
                'submission_message' => 'submission message',
                'documentation' => 'some documentation',
                'documentation_label' => 'Documentation label',
                'duration' => '5 years',
                'deadline_at' => '2024-07-08T17:00:00.000000Z',
                'stage_id' => $stage->id,
            ])
            ->assertJsonPath('data.form_sections.0.title', 'Form section 1')
            ->assertJsonPath('data.form_sections.0.form_questions.0.label', 'Question 1 label')
            ->assertJsonPath('data.form_sections.0.form_questions.1.label', 'Question 2 label')
            ->assertJsonPath('data.form_sections.0.form_questions.0.children.0.label', 'Child Question 1 label')
            ->assertJsonPath('data.form_sections.0.form_questions.0.children.1.label', 'Child Question 2 label')
            ->assertJsonPath('data.form_sections.0.form_questions.1.options.0.label', 'Option 1')
            ->assertJsonPath('data.form_sections.0.form_questions.1.options.1.label', 'Option 2')
            ->assertJsonPath('data.form_sections.0.form_questions.2.options.2.label', $options[0]->label)
            ->assertJsonPath('data.form_sections.0.form_questions.2.options.3.label', $options[1]->label)
            ->assertJsonPath('data.form_sections.0.form_questions.2.options.4.label', $options[2]->label);
    }

    public function test_admins_can_create_forms_with_conditional_fields(): void
    {
        $user = User::factory()->admin()->create();
        $stage = Stage::factory()->create();
        $optionList = FormOptionList::factory()->create();
        $options = FormOptionListOption::factory(3)->create([
            'form_option_list_id' => $optionList,
        ]);

        $payload = [
            'title' => 'form',
            'subtitle' => 'form subtitle',
            'description' => 'description',
            'submission_message' => 'submission message',
            'documentation' => 'some documentation',
            'documentation_label' => 'Documentation label',
            'duration' => '5 years',
            'deadline_at' => '2024-07-08 12:00:00',
            'stage_id' => $stage->id,
            'published' => true,
            'form_sections' => [
                [
                    'order' => 1,
                    'title' => 'Form section 1',
                    'subtitle' => 'Form section 1 subtitle',
                    'description' => 'Form section 1 description',
                    'form_questions' => [
                        [
                            'linked_field_key' => null,
                            'label' => 'Question 1 label',
                            'placeholder' => 'Question 1 placeholder',
                            'description' => 'Question 1 description',
                            'input_type' => 'conditional',
                            'validation' => [
                                'required' => true,
                            ],
                            'order' => 1,
                            'child_form_questions' => [
                                [
                                    'show_on_parent_condition' => true,
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
                                    'show_on_parent_condition' => false,
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
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/forms', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.form_sections.0.form_questions.0.linked_field_key', null)
            ->assertJsonPath('data.form_sections.0.form_questions.0.input_type', 'conditional')
            ->assertJsonPath('data.form_sections.0.form_questions.0.children.0.show_on_parent_condition', true)
            ->assertJsonPath('data.form_sections.0.form_questions.0.children.1.show_on_parent_condition', false);
    }

    public function testNonAdminsCannotCreateForms()
    {
        $user = User::factory()->create();
        $stage = Stage::factory()->create();

        $payload = [
            'title' => 'form',
            'subtitle' => 'form subtitle',
            'description' => 'description',
            'submission_message' => 'submission message',
            'documentation' => 'some documentation',
            'duration' => '5 years',
            'stage_id' => $stage->id,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/forms', $payload)
            ->assertStatus(403);
    }
}
