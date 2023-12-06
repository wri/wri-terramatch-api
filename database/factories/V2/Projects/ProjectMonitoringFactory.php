<?php

namespace Database\Factories\V2\Projects;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectMonitoringFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $haInterventionTypeFields = [
            'ha_mangrove',
            'ha_assisted',
            'ha_agroforestry',
            'ha_reforestation',
            'ha_peatland',
            'ha_riparian',
            'ha_enrichment',
            'ha_nucleation',
            'ha_silvopasture',
            'ha_direct',
        ];
        $frameworks = ['ppc', 'terrafund'];

        $data = [
            'framework_key' => $this->faker->randomElement($frameworks),
            'status' => ProjectMonitoring::STATUS_ACTIVE,
            'uuid' => Str::uuid()->toString(),
            'project_id' => Project::factory()->create()->id,

            'tree_count' => $this->faker->randomFloat(2, 0, 10000),
            'tree_cover' => $this->faker->randomFloat(2, 0, 100),
            'tree_cover_loss' => $this->faker->randomFloat(2, 0, 100),
            'carbon_benefits' => $this->faker->randomFloat(2, 0, 10000),
            'number_of_esrp' => $this->faker->randomFloat(2, 0, 8),

            'field_tree_count' => $this->faker->randomFloat(2, 0, 10000),
            'field_tree_regenerated' => $this->faker->randomFloat(2, 0, 10000),
            'field_tree_survival_percent' => $this->faker->randomFloat(2, 0, 100),
        ];

        $haTypes = $this->faker->randomElements($haInterventionTypeFields, $this->faker->numberBetween(1, 5));

        foreach ($haTypes as $haType) {
            $data[$haType] = $this->faker->randomFloat(2, 0, 30);
        }

        return $data;
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
