<?php

namespace Database\Factories\V2\Projects;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $frameworks = ['ppc', 'terrafund'];

        return [
            'framework_key' => $this->faker->randomElement($frameworks),
            'project_id' => Project::factory()->create(),
            'due_at' => $this->faker->dateTime,
            'title' => $this->faker->text(30),
            'status' => array_keys(ProjectReport::$statuses)[0],
            'completion' => $this->faker->numberBetween(0, 100),
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
