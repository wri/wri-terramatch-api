<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundNurseryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'start_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('-2 years', '-1 day')->format('Y-m-d'),
            'seedling_grown' => $this->faker->randomNumber(5, false),
            'planting_contribution' => $this->faker->paragraph(),
            'nursery_type' => 'managing',
            'terrafund_programme_id' => TerrafundProgramme::factory()->create()->id,
        ];
    }
}
