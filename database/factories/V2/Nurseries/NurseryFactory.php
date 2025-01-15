<?php

namespace Database\Factories\V2\Nurseries;

use App\Models\V2\Projects\Project;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = ['manageing', 'building', 'expanding'];
        $frameworks = ['ppc', 'terrafund'];
        $frameworkKey = $this->faker->randomElement($frameworks);

        return [
            'framework_key' => $frameworkKey,
            'project_id' => Project::factory()->create(['framework_key' => $frameworkKey])->id,
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
            'type' => $this->faker->randomElement($types),
            'name' => $this->faker->word(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'seedling_grown' => $this->faker->numberBetween(0, 9999999),
            'planting_contribution' => $this->faker->text(500),
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
