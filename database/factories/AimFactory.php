<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class AimFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'year_five_trees' => $this->faker->numberBetween(0, 500),
            'restoration_hectares' => $this->faker->numberBetween(0, 500),
            'survival_rate' => $this->faker->numberBetween(0, 500),
            'year_five_crown_cover' => $this->faker->numberBetween(0, 500),
            'programme_id' => Programme::factory()->create(),
        ];
    }
}
