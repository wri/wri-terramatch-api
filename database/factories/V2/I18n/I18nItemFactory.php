<?php

namespace Database\Factories\V2\I18n;

use Illuminate\Database\Eloquent\Factories\Factory;

class I18nItemFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'type' => $this->faker->randomElement(['short', 'long']),
            'status' => $this->faker->randomElement(['draft', 'pending', 'translated', 'modified']),
            'short_value' => $this->faker->sentence(),
            'long_value' => $this->faker->paragraph(),
            'hash' => $this->faker->md5(),
        ];
    }
}
