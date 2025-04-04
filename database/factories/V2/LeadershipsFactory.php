<?php

namespace Database\Factories\V2;

use App\Models\V2\Leaderships;
use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadershipsFactory extends Factory
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
            'position' => $this->faker->word(),
            'gender' => $this->faker->word(),
            'nationality' => $this->faker->word(),
            'age' => $this->faker->numberBetween(0, 150),
            'collection' => $this->faker->randomElement(Leaderships::$collections),
        ];
    }
}
