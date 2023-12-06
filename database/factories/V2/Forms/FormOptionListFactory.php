<?php

namespace Database\Factories\V2\Forms;

use Illuminate\Database\Eloquent\Factories\Factory;

class FormOptionListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'key' => $this->faker->unique()->word(),
        ];
    }
}
