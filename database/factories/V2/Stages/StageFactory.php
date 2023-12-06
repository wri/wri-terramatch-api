<?php

namespace Database\Factories\V2\Stages;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'funding_programme_id' => FundingProgramme::factory()->create()->uuid,
            'order' => $this->faker->numberBetween(1, 4),
            'name' => $this->faker->name,
            'status' => $this->faker->randomElement(array_keys(Stage::$statuses)),
        ];
    }
}
