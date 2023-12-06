<?php

namespace Database\Factories\V2\Forms;

use App\Models\V2\Forms\FormSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'input_type' => $this->faker->randomElement(['date', 'text', 'long-text', 'select', 'checkboxes', 'radio', 'number', 'image', 'file']), // this will change once we've finished adding types
            'label' => $this->faker->word(),
            'description' => $this->faker->text(200),
            'placeholder' => $this->faker->text(30),
            'validation' => [
                'required' => $this->faker->boolean(),
            ],
            'name' => $this->faker->word(),
            'multichoice' => false,
            'order' => $this->faker->numberBetween(1, 127),
            'form_section_id' => FormSection::factory()->create(),
        ];
    }

    public function conditionalField(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'input_type' => 'conditional',
                'linked_field_key' => null,
            ];
        });
    }
}
