<?php

namespace App\Resources;

use App\Models\Pitch as ParentModel;
use App\Models\PitchVersion as ChildModel;

class PitchVersionResource extends Resource
{
    public function __construct(ParentModel $parentModel, ChildModel $childModel)
    {
        $this->id = $childModel->id;
        $this->status = $childModel->status;
        $this->approved_rejected_by = $childModel->approved_rejected_by;
        $this->approved_rejected_at = $childModel->approved_rejected_at;
        $this->rejected_reason = $childModel->rejected_reason;
        $this->rejected_reason_body = $childModel->rejected_reason_body;
        $this->created_at = $childModel->created_at;
        $this->updated_at = $childModel->updated_at;
        $this->data = (object) [
            'id' => $parentModel->id,
            'organisation_id' => $parentModel->organisation_id,
            'name' => $childModel->name,
            'description' => $childModel->description,
            'land_types' => $childModel->land_types,
            'land_ownerships' => $childModel->land_ownerships,
            'land_size' => $childModel->land_size,
            'land_continent' => $childModel->land_continent,
            'land_country' => $childModel->land_country,
            'land_geojson' => $childModel->land_geojson,
            'restoration_methods' => $childModel->restoration_methods,
            'restoration_goals' => $childModel->restoration_goals,
            'funding_sources' => $childModel->funding_sources,
            'funding_amount' => $childModel->funding_amount,
            'funding_bracket' => $childModel->funding_bracket,
            'revenue_drivers' => $childModel->revenue_drivers,
            'estimated_timespan' => $childModel->estimated_timespan,
            'long_term_engagement' => $childModel->long_term_engagement,
            'reporting_frequency' => $childModel->reporting_frequency,
            'reporting_level' => $childModel->reporting_level,
            'sustainable_development_goals' => $childModel->sustainable_development_goals,
            'cover_photo' => $childModel->cover_photo,
            'avatar' => $parentModel->organisation->approved_version->avatar,
            'video' => $childModel->video,
            'problem' => $childModel->problem,
            'anticipated_outcome' => $childModel->anticipated_outcome,
            'who_is_involved' => $childModel->who_is_involved,
            'local_community_involvement' => $childModel->local_community_involvement,
            'training_involved' => $childModel->training_involved,
            'training_type' => $childModel->training_type,
            'training_amount_people' => $childModel->training_amount_people,
            'people_working_in' => $childModel->people_working_in,
            'people_amount_nearby' => $childModel->people_amount_nearby,
            'people_amount_abroad' => $childModel->people_amount_abroad,
            'people_amount_employees' => $childModel->people_amount_employees,
            'people_amount_volunteers' => $childModel->people_amount_volunteers,
            'benefited_people' => $childModel->benefited_people,
            'future_maintenance' => $childModel->future_maintenance,
            'use_of_resources' => $childModel->use_of_resources,
            'facebook' => $childModel->facebook,
            'twitter' => $childModel->twitter,
            'instagram' => $childModel->instagram,
            'linkedin' => $childModel->linkedin,
            'price_per_tree' => $childModel->price_per_tree,
            'created_at' => $parentModel->created_at,
            'updated_at' => $childModel->created_at,
            'successful' => in_array($parentModel->visibility, ['fully_invested_funded', 'finished']),
            'visibility' => $parentModel->visibility,
        ];
    }
}
