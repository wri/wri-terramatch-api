<?php

namespace Database\Factories\V2;

use App\Models\V2\Sites\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvasiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'invasiveable_type' => Site::class,
            'invasiveable_id' => Site::factory()->create(),
            'name' => $this->faker->text(200),
            'type' => $this->faker->randomElement(['common', 'uncommon', 'dominant_species']),
        ];
    }
}
