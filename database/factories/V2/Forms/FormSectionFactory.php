<?php

namespace Database\Factories\V2\Forms;

use App\Models\V2\Forms\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'order' => $this->faker->numberBetween(1, 255),
            'form_id' => Form::factory()->create()->uuid,
            'title' => $this->faker->text(50),
            'subtitle' => $this->faker->text(100),
            'description' => $this->faker->text(300),
        ];
    }
}
