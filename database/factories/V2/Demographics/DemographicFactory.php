<?php

namespace Database\Factories\V2\Demographics;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemographicFactory extends Factory
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
            'demographical_type' => SiteReport::class,
            'demographical_id' => SiteReport::factory()->create(),
            'type' => Demographic::WORKDAY_TYPE,
            'collection' => $this->faker->randomElement(array_keys(DemographicCollections::WORKDAYS_SITE_COLLECTIONS)),
        ];
    }

    public function projectReportWorkdays(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'demographical_type' => ProjectReport::class,
                'demographical_id' => ProjectReport::factory()->create(),
                'type' => Demographic::WORKDAY_TYPE,
                'collection' => $this->faker->randomElement(array_keys(DemographicCollections::WORKDAYS_PROJECT_COLLECTIONS)),
            ];
        });
    }
}
