<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundDisturbanceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'disturbanceable_type' => TerrafundSiteSubmission::class,
            'disturbanceable_id' => TerrafundSiteSubmission::factory()->create()->id,
            'type' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
