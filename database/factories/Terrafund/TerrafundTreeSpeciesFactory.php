<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundTreeSpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'amount' => $this->faker->numberBetween(1),
            'treeable_type' => TerrafundSite::class,
            'treeable_id' => TerrafundSite::factory()->create(),
        ];
    }

    public function terrafundNursery()
    {
        return $this->state(function (array $attributes) {
            return [
                'treeable_type' => TerrafundNursery::class,
                'treeable_id' => TerrafundNursery::factory()->create(),
        ];
        });
    }
}
