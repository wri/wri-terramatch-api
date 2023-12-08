<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->word(),
            'programme_id' => Programme::factory()->create(),

//            'establishment_date' => $this->faker->date(),
//            'end_date' => $this->faker->date(),
//            'aim_survival_rate' => $this->faker->numberBetween(0, 100),
//            'aim_year_five_crown_cover' => $this->faker->numberBetween(0, 100),
//            'aim_direct_seeding_survival_rate' => $this->faker->numberBetween(0, 100),
//            'aim_natural_regeneration_trees_per_hectare' => $this->faker->numberBetween(0, 10000),
//            'aim_soil_condition' => $this->faker->randomElement(['severely_degraded','poor','fair','good','no_degradation']),
//            'aim_number_of_mature_trees' => $this->faker->numberBetween(0, 10000),
//            'planting_pattern' => $this->faker->text(300),
//            'stratification_for_heterogeneity' => $this->faker->word,
//            'aim_natural_regeneration_hectares' => $this->faker->numberBetween(0, 100),
        ];
    }
}
