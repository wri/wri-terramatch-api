<?php

namespace Database\Factories\V2;

use App\Models\V2\FundingProgramme;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedExportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word() . '.csv',
            'funding_programme_id' => FundingProgramme::factory()->create(),
        ];
    }
}
