<?php

namespace Database\Factories;

use App\Models\Drafting\DraftProgrammeSubmission;
use App\Models\Drafting\DraftSiteSubmission;
use App\Models\Drafting\DraftTerrafundProgramme;
use App\Models\DueSubmission;
use App\Models\Organisation;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DraftFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'organisation_id' => Organisation::factory()->create()->id,
            'name' => $this->faker->word(),
            'type' => 'terrafund_programme',
            'data' => json_encode(DraftTerrafundProgramme::BLUEPRINT),
            'created_by' => User::factory()->create()->id,
            'updated_by' => null,
            'is_from_mobile' => null,
            'is_merged' => false,
            'due_submission_id' => null,
        ];
    }

    public function programmeSubmission()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'programme_submission',
                'data' => json_encode(DraftProgrammeSubmission::BLUEPRINT),
            ];
        });
    }

    public function siteSubmission()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'site_submission',
                'data' => json_encode(DraftSiteSubmission::BLUEPRINT),
                'due_submission_id' => DueSubmission::factory()->create(),
            ];
        });
    }

    public function terrafundProgrammeSubmission()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'terrafund_programme_submission',
                'data' => json_encode(['terrafund_programme_submission' => TerrafundProgrammeSubmission::factory()->make(), 'photos' => []]),
            ];
        });
    }

    public function terrafundSiteSubmission()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'terrafund_site_submission',
                'data' => json_encode(['terrafund_site_submission' => TerrafundSiteSubmission::factory()->make(), 'photos' => []]),
            ];
        });
    }

    public function terrafundNurserySubmission()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'terrafund_nursery_submission',
                'data' => json_encode(['terrafund_nursery_submission' => TerrafundNurserySubmission::factory()->make(), 'photos' => []]),
            ];
        });
    }
}
