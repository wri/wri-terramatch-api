<?php

namespace Database\Factories\V2\TreeSpecies;

use App\Models\V2\Projects\Project;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreeSpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'speciesable_type' => Project::class,
            'speciesable_id' => Project::factory()->create(),
            'name' => $this->faker->word(),
            'amount' => $this->faker->numberBetween(0, 2147483647),
            'collection' => $this->faker->randomElement(TreeSpecies::$collections),
        ];
    }
}
