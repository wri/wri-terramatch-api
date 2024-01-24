<?php

namespace Database\Factories\V2;

use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OwnershipStakeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'organisation_id' => Organisation::factory()->create()->uuid,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'title' => $this->faker->title,
            'gender' => $this->faker->word(),
            'percent_ownership' => $this->faker->numberBetween(0, 100),
            'year_of_birth' => $this->faker->numberBetween(1990, 2050),
        ];
    }
}
