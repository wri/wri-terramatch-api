<?php

namespace Database\Factories\V2;

use App\Models\V2\Action;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => Action::TYPE_TASK,
            'status' => Action::STATUS_PENDING,
            'organisation_id' => Organisation::factory()->create(),
            'targetable_type' => Project::class,
            'targetable_id' => Project::factory()->create(),
        ];
    }

    public function complete()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Action::STATUS_COMPLETE,
            ];
        });
    }

    public function project()
    {
        return $this->state(function (array $attributes) {
            return [
                'targetable_id' => Project::factory(),
                'targetable_type' => Project::class,
            ];
        });
    }

    public function projectReport()
    {
        return $this->state(function (array $attributes) {
            return [
                'targetable_id' => ProjectReport::factory(),
                'targetable_type' => ProjectReport::class,
            ];
        });
    }

    public function site()
    {
        return $this->state(function (array $attributes) {
            return [
                'targetable_id' => Site::factory(),
                'targetable_type' => Site::class,
            ];
        });
    }

    public function siteReport()
    {
        return $this->state(function (array $attributes) {
            return [
                'targetable_id' => SiteReport::factory(),
                'targetable_type' => SiteReport::class,
            ];
        });
    }

    public function nursery()
    {
        return $this->state(function (array $attributes) {
            return [
                'targetable_id' => Nursery::factory(),
                'targetable_type' => Nursery::class,
            ];
        });
    }

    public function nurseryReport()
    {
        return $this->state(function (array $attributes) {
            return [
                'targetable_id' => NurseryReport::factory(),
                'targetable_type' => NurseryReport::class,
            ];
        });
    }
}
