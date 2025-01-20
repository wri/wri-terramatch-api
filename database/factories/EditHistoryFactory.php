<?php

namespace Database\Factories;

use App\Models\EditHistory;
use App\Models\OrganisationVersion;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class EditHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        $orgVersion = OrganisationVersion::factory()->create();
        $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
        $when = $this->faker->dateTimeBetween('-2 months', 'now');

        return [
            'uuid' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(EditHistory::$statuses),//EditHistory::$statuses ),
            'content' => json_encode(Programme::factory()->make(['organisation_id' => $orgVersion->organisation_id])),

            'organisation_id' => $orgVersion->organisation_id,
            'projectable_type' => \App\Models\Programme::class,
            'projectable_id' => $project->id,
            'project_name' => $project->name,
            'editable_id' => $project->id,
            'editable_type' => \App\Models\Programme::class,
            'framework_id' => 1,
            'created_by_user_id' => User::factory()->create()->id,
            'created_at' => $when,
            'updated_at' => $when,
        ];
    }

    public function programme(array $override = null)
    {
        return $this->state(function (array $attributes) use ($override) {
            $orgVersion = OrganisationVersion::factory()->create();
            $orgPart = ['organisation_id' => $orgVersion->organisation_id];

            $project = Programme::factory()->create($override ? array_merge($override, $orgPart) : $orgPart);
            $programme = Programme::factory()->make($override ?? []);

            return [
                'framework_id' => $project->framework_id,
                'projectable_type' => get_class($project),
                'projectable_id' => $project->id,
                'project_name' => $project->name,
                'editable_type' => get_class($project),
                'editable_id' => $project->id,
                'content' => json_encode(Arr::except((array) $programme, ['boundary_geojson', 'organisation_id', 'framework_id'])),
            ];
        });
    }

    public function site()
    {
        return $this->state(function (array $attributes) {
            $orgVersion = OrganisationVersion::factory()->create();
            $project = Programme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
            $site = Site::factory()->create(['programme_id' => $project->id]);

            return [
                'framework_id' => $project->framework_id,
                'projectable_type' => get_class($project),
                'projectable_id' => $project->id,
                'project_name' => $project->name,
                'editable_type' => get_class($site),
                'editable_id' => $site->id,
                'content' => json_encode(Site::factory()->make(['programme_id' => $project->id])),
            ];
        });
    }

    // public function terrafundSite()
    // {
    //     return $this->state(function (array $attributes) {
    //         $orgVersion = OrganisationVersion::factory()->create();
    //         $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
    //         $site = TerrafundSite::factory()->create(['terrafund_programme_id' => $project->id]);

    //         return [
    //             'framework_id' => $project->framework_id,
    //             'projectable_type' => get_class($project),
    //             'projectable_id' => $project->id,
    //             'project_name' => $project->name,
    //             'editable_type' => get_class($site),
    //             'editable_id' => $site->id,
    //             'content' => json_encode((array) TerrafundSite::factory()->make()),
    //         ];
    //     });
    // }

    // public function terrafundNursery()
    // {
    //     return $this->state(function (array $attributes) {
    //         $orgVersion = OrganisationVersion::factory()->create();
    //         $project = TerrafundProgramme::factory()->create(['organisation_id' => $orgVersion->organisation_id]);
    //         $nursery = TerrafundNursery::factory()->create(['terrafund_programme_id' => $project->id]);

    //         return [
    //             'framework_id' => $project->framework_id,
    //             'projectable_type' => get_class($project),
    //             'projectable_id' => $project->id,
    //             'project_name' => $project->name,
    //             'editable_type' => get_class($nursery),
    //             'editable_id' => $nursery->id,
    //             'content' => json_encode((array) TerrafundNursery::factory()->make()),
    //         ];
    //     });
    // }
}
