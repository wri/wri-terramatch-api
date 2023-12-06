<?php

namespace Database\Factories\V2;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(array_keys(FundingProgramme::$statuses)),
            'description' => $this->faker->paragraph(),
            'read_more_url' => $this->faker->url(),
            'location' => $this->faker->country(),
            'organisation_types' => $this->faker->randomElements(array_keys(Organisation::$types), 2),
        ];
    }
}
