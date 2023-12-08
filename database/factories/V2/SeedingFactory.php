<?php

namespace Database\Factories\V2;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeedingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $seedableClass = $this->faker->randomElement([Site::class, SiteReport::class]);

        return [
            'uuid' => $this->faker->uuid,
            'seedable_id' => $seedableClass::factory(),
            'name' => $this->faker->text(200),
            'seeds_in_sample' => $this->faker->numberBetween(0, 100),
            'weight_of_sample' => $this->faker->numberBetween(0, 100),
            'amount' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function site(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'seedable_id' => Site::factory(),
            ];
        });
    }

    public function siteReport(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'seedable_id' => SiteReport::factory(),
            ];
        });
    }
}
