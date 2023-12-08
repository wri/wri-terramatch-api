<?php

namespace Database\Factories\V2;

use App\Models\V2\Sites\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisturbanceFactory extends Factory
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
            'disturbanceable_type' => Site::class,
            'disturbanceable_id' => Site::factory()->create(),
            'description' => $this->faker->text(200),
            'type' => $this->faker->randomElement(['ecological', 'climatic', 'manmade']),
            'intensity' => $this->faker->randomElement(['low','medium', 'high']),
            'extent' => $this->faker->randomElement(['0-20', '21-40', '41-60', '61-80', '81-100']),
            'collection' => 'disturbance',
        ];
    }
}
