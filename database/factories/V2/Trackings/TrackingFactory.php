<?php

namespace Database\Factories\V2\Trackings;

use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Trackings\DemographicCollections;
use App\Models\V2\Trackings\Tracking;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingFactory extends Factory
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
            'trackable_type' => SiteReport::class,
            'trackable_id' => SiteReport::factory()->create(),
            'domain' => 'demographics',
            'type' => Tracking::WORKDAY_TYPE,
            'collection' => $this->faker->randomElement(collect(array_values(SiteReport::DEMOGRAPHIC_COLLECTIONS[Tracking::WORKDAY_TYPE]))->flatten()),
        ];
    }

    public function projectReportWorkdays(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'trackable_type' => ProjectReport::class,
                'trackable_id' => ProjectReport::factory()->create(),
                'domain' => 'demographics',
                'type' => Tracking::WORKDAY_TYPE,
                'collection' => $this->faker->randomElement(collect(array_values(ProjectReport::DEMOGRAPHIC_COLLECTIONS[Tracking::WORKDAY_TYPE]))->flatten()),
            ];
        });
    }

    public function projectReportRestorationPartners(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'trackable_type' => ProjectReport::class,
                'trackable_id' => ProjectReport::factory()->create(),
                'domain' => 'demographics',
                'type' => Tracking::RESTORATION_PARTNER_TYPE,
                'collection' => $this->faker->randomElement(collect(array_values(ProjectReport::DEMOGRAPHIC_COLLECTIONS[Tracking::RESTORATION_PARTNER_TYPE]))->flatten()),
            ];
        });
    }

    public function projectPitchJobs(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'trackable_type' => ProjectPitch::class,
                'trackable_id' => ProjectPitch::factory()->create(),
                'domain' => 'demographics',
                'type' => Tracking::JOBS_TYPE,
                'collection' => DemographicCollections::ALL,
            ];
        });
    }

    public function projectVolunteers(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'trackable_type' => Project::class,
                'trackable_id' => Project::factory()->create(),
                'domain' => 'demographics',
                'type' => Tracking::VOLUNTEERS_TYPE,
                'collection' => DemographicCollections::VOLUNTEER,
            ];
        });
    }
}
