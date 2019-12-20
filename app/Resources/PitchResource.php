<?php

namespace App\Resources;

use App\Models\Pitch as ParentModel;
use App\Models\PitchVersion as ChildModel;

class PitchResource extends Resource
{
    public $id = null;
    public $organisation_id = null;
    public $name = null;
    public $description = null;
    public $land_types = [];
    public $land_ownerships = [];
    public $land_size = null;
    public $land_continent = null;
    public $land_country = null;
    public $land_geojson = null;
    public $restoration_methods = [];
    public $restoration_goals = [];
    public $funding_sources = [];
    public $funding_amount = null;
    public $revenue_drivers = [];
    public $estimated_timespan = null;
    public $long_term_engagement = null;
    public $reporting_frequency = null;
    public $reporting_level = null;
    public $sustainable_development_goals = [];
    public $cover_photo = null;
    public $avatar = null;
    public $video = null;
    public $created_at = null;

    public function __construct(ParentModel $parentModel, ?ChildModel $childModel, bool $displayCompatibilityScore = false)
    {
        $this->id = $parentModel->id;
        $this->organisation_id = $parentModel->organisation_id;
        $this->name = $childModel->name ?? null;
        $this->description = $childModel->description ?? null;
        $this->land_types = $childModel->land_types ?? [];
        $this->land_ownerships = $childModel->land_ownerships ?? [];
        $this->land_size = $childModel->land_size ?? null;
        $this->land_continent = $childModel->land_continent ?? null;
        $this->land_country = $childModel->land_country ?? null;
        $this->land_geojson = $childModel->land_geojson ?? null;
        $this->restoration_methods = $childModel->restoration_methods ?? [];
        $this->restoration_goals = $childModel->restoration_goals ?? [];
        $this->funding_sources = $childModel->funding_sources ?? [];
        $this->funding_amount = $childModel->funding_amount ?? null;
        $this->revenue_drivers = $childModel->revenue_drivers ?? [];
        $this->estimated_timespan = $childModel->estimated_timespan ?? null;
        $this->long_term_engagement = $childModel->long_term_engagement ?? null;
        $this->reporting_frequency = $childModel->reporting_frequency ?? null;
        $this->reporting_level = $childModel->reporting_level ?? null;
        $this->sustainable_development_goals = $childModel->sustainable_development_goals ?? [];
        $this->cover_photo = $childModel->cover_photo ?? null;
        $this->avatar = $childModel->avatar ?? null;
        $this->video = $childModel->video ?? null;
        $this->problem = $childModel->problem ?? null;
        $this->anticipated_outcome = $childModel->anticipated_outcome ?? null;
        $this->who_is_involved = $childModel->who_is_involved ?? null;
        $this->local_community_involvement = $childModel->local_community_involvement ?? null;
        $this->training_involved = $childModel->training_involved ?? null;
        $this->training_type = $childModel->training_type ?? null;
        $this->training_amount_people = $childModel->training_amount_people ?? null;
        $this->people_working_in = $childModel->people_working_in ?? null;
        $this->people_amount_nearby = $childModel->people_amount_nearby ?? null;
        $this->people_amount_abroad = $childModel->people_amount_abroad ?? null;
        $this->people_amount_employees = $childModel->people_amount_employees ?? null;
        $this->people_amount_volunteers = $childModel->people_amount_volunteers ?? null;
        $this->benefited_people = $childModel->benefited_people ?? null;
        $this->future_maintenance = $childModel->future_maintenance ?? null;
        $this->use_of_resources = $childModel->use_of_resources ?? null;
        $this->facebook = $childModel->facebook ?? null;
        $this->twitter = $childModel->twitter ?? null;
        $this->instagram = $childModel->instagram ?? null;
        $this->created_at = $parentModel->created_at;
        $this->completed = $parentModel->completed;
        $this->completed_by = $parentModel->completed_by;
        $this->completed_at = $parentModel->completed_at;
        $this->successful = $parentModel->successful;
        if ($displayCompatibilityScore) {
            $this->compatibility_score = (int) $childModel->compatibility_score;
        }
    }
}
