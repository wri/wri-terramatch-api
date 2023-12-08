<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundNoneTreeSpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'amount' => $this->faker->numberBetween(1),
            'speciesable_type' => TerrafundSiteSubmission::class,
            'speciesable_id' => TerrafundSiteSubmission::create(['terrafund_site_id' => TerrafundSite::factory()->create()->id])->id,
        ];
    }
}
