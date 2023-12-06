<?php

namespace Database\Factories\V2\Sites;

use App\Models\V2\Sites\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteMonitoringFactory extends Factory
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
            'uuid' => $this->faker->uuid,
            'framework_key' => $this->faker->randomElement($frameworks),
            'site_id' => Site::factory()->create()->id,
            'status' => 'active',
            'tree_count' => $this->faker->randomFloat(2, 0, 999999),
            'tree_cover' => $this->faker->randomFloat(2, 0, 999999),
            'field_tree_count' => $this->faker->randomFloat(2, 0, 999999),
            'measurement_date' => $this->faker->date(),
            'last_updated' => $this->faker->dateTime(),
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
