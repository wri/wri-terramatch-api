<?php

namespace Database\Factories\V2\Workdays;

use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkdayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'workdayable_type' => SiteReport::class,
            'workdayable_id' => SiteReport::factory()->create(),
            'collection' => $this->faker->randomElement(array_keys(Workday::SITE_COLLECTIONS)),
        ];
    }

    public function projectReport(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'workdayable_type' => ProjectReport::class,
                'workdayable_id' => ProjectReport::factory()->create(),
                'collection' => $this->faker->randomElement(array_keys(Workday::PROJECT_COLLECTION)),
            ];
        });
    }
}
