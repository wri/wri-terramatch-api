<?php

namespace Database\Factories\V2;

use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingTypeFactory extends Factory
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
            'amount' => $this->faker->numberBetween(0, 9999999),
            'year' => $this->faker->numberBetween(1990, 2050),
            'source' => $this->faker->name,
            'type' => $this->faker->randomElement(array_keys(config('wri.funding-types'))),
        ];
    }
}
