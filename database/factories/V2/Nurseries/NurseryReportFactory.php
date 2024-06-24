<?php

namespace Database\Factories\V2\Nurseries;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseryReportFactory extends Factory
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
            'nursery_id' => Nursery::factory()->create()->id,
            'status' => array_keys(NurseryReport::$statuses)[0],
            'title' => $this->faker->text(30),
            'seedlings_young_trees' => $this->faker->numberBetween(0, 9999999),
            'interesting_facts' => $this->faker->text(500),
            'site_prep' => $this->faker->text(500),
            'due_at' => $this->faker->dateTime,
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
