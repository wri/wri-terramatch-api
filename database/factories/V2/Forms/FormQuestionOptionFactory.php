<?php

namespace Database\Factories\V2\Forms;

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormQuestionOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'form_question_id' => FormQuestion::factory()->create(),
            'label' => $this->faker->word(),
            'slug' => $this->faker->word(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
