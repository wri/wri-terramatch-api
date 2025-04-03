<?php

namespace Database\Factories\V2\Demographics;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\ProjectPitch;
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
            'collection' => $this->faker->randomElement(collect(array_values(SiteReport::DEMOGRAPHIC_COLLECTIONS[Demographic::WORKDAY_TYPE]))->flatten()),
        ];
    }

    public function projectReportWorkdays(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'demographical_type' => ProjectReport::class,
                'demographical_id' => ProjectReport::factory()->create(),
                'type' => Demographic::WORKDAY_TYPE,
                'collection' => $this->faker->randomElement(collect(array_values(ProjectReport::DEMOGRAPHIC_COLLECTIONS[Demographic::WORKDAY_TYPE]))->flatten()),
            ];
        });
    }

    public function projectReportRestorationPartners(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'demographical_type' => ProjectReport::class,
                'demographical_id' => ProjectReport::factory()->create(),
                'type' => Demographic::RESTORATION_PARTNER_TYPE,
                'collection' => $this->faker->randomElement(collect(array_values(ProjectReport::DEMOGRAPHIC_COLLECTIONS[Demographic::RESTORATION_PARTNER_TYPE]))->flatten()),
            ];
        });
    }

    public function projectPitchEmployees(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'demographical_type' => ProjectPitch::class,
                'demographical_id' => ProjectPitch::factory()->create(),
                'type' => Demographic::EMPLOYEES_TYPE,
                'collection' => DemographicCollections::ALL
            ];
        });
    }
}
