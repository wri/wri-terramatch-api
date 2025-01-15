<?php

namespace Database\Factories\V2\Sites;

use App\Models\V2\Projects\Project;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $soilCondition = [
            'severely_degraded',
            'poor',
            'fair',
            'good',
            'no_degradation',
        ];
        $frameworks = [
            'ppc',
            'terrafund',
        ];
        $landUse = [
            'agroforest',
            'mangrove',
            'natural-forest',
            'silvopasture',
            'riparian-area-or-wetland',
            'urban-forest',
            'woodlot-or-plantation',
        ];
        $restorationStrat = [
            'assisted-natural-regeneration',
            'direct-seeding',
            'tree-planting',
        ];
        $landTenures = [
            'national_protected_area',
            'indigenous',
            'private',
        ];
        $timeStamp = $this->faker->dateTimeBetween('-2 months', 'now');

        return [
            'uuid' => $this->faker->uuid,
            'framework_key' => $this->faker->randomElement($frameworks),
            'project_id' => Project::factory()->create()->id,
            'name' => $this->faker->words(3, true),
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
            'control_site' => $this->faker->boolean(15),
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
            'land_use_types' => $this->faker->randomElements(
                $landUse,
                $this->faker->numberBetween(0, 7),
                false
            ),
            'restoration_strategy' => $this->faker->randomElements(
                $restorationStrat,
                $this->faker->numberBetween(0, 3),
                false
            ),
            'description' => $this->faker->text(500),
            'history' => $this->faker->paragraph(3),
            'land_tenures' => $this->faker->randomElement($landTenures),
            'landscape_community_contribution' => $this->faker->paragraph(),
            'planting_pattern' => $this->faker->text(300),
            'soil_condition' => $this->faker->randomElement($soilCondition),
            'survival_rate_planted' => $this->faker->numberBetween(0, 100),
            'direct_seeding_survival_rate' => $this->faker->numberBetween(0, 100),
            'a_nat_regeneration_trees_per_hectare' => $this->faker->numberBetween(0, 100),
            'a_nat_regeneration' => $this->faker->numberBetween(0, 100),
            'hectares_to_restore_goal' => $this->faker->numberBetween(0, 100),
            'aim_year_five_crown_cover' => $this->faker->numberBetween(0, 100),
            'aim_number_of_mature_trees' => $this->faker->numberBetween(0, 100),
            'start_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'end_date' => $this->faker->dateTimeBetween('-2 years', '-1 day'),
            'created_at' => $timeStamp,
            'updated_at' => $timeStamp,
            'deleted_at' => null,
        ];
    }

    public function ppc(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'framework_key' => 'ppc',
            ];
        });
    }

    public function terrafund(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'framework_key' => 'terrafund',
            ];
        });
    }
}
