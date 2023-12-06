<?php

namespace Database\Factories\V2\Stratas;

use App\Models\V2\Sites\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class StrataFactory extends Factory
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
            'stratasable_type' => Site::class,
            'stratasable_id' => Site::factory()->create(),
            'description' => $this->faker->text(200),
            'extent' => $this->faker->numberBetween(0, 100),
        ];
    }
}
