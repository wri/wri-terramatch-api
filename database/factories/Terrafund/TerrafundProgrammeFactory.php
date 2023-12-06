<?php

namespace Database\Factories\Terrafund;

use App\Models\Framework;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        $framework = Framework::where('name', 'Terrafund')
            ->first();
        if (empty($framework)) {
            $framework = Framework::factory()->create([
                'name' => 'Terrafund',
            ]);
        }

        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
            'planting_start_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'planting_end_date' => $this->faker->dateTimeBetween('-2 years', '-1 day')->format('Y-m-d'),
            'budget' => $this->faker->randomNumber(5, false),
            'status' => 'new_project',
            'home_country' => 'se',
            'project_country' => 'au',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
            'history' => $this->faker->paragraph(),
            'objectives' => $this->faker->paragraph(),
            'environmental_goals' => $this->faker->paragraph(),
            'socioeconomic_goals' => $this->faker->paragraph(),
            'sdgs_impacted' => $this->faker->paragraph(),
            'long_term_growth' => $this->faker->paragraph(),
            'community_incentives' => $this->faker->paragraph(),
            'total_hectares_restored' => $this->faker->randomNumber(5, false),
            'trees_planted' => $this->faker->randomNumber(5, false),
            'jobs_created' => $this->faker->randomNumber(5, false),
            'framework_id' => $framework->id,
            'organisation_id' => Organisation::factory()->create(),
        ];
    }
}
