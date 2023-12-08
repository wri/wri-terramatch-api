<?php

namespace App\Resources;

use App\Models\Organisation as ParentModel;
use App\Models\OrganisationVersion as ChildModel;

class OrganisationVersionResource extends Resource
{
    public function __construct(ParentModel $parentModel, ChildModel $childModel)
    {
        $this->id = $childModel->id;
        $this->status = $childModel->status;
        $this->account_type = $childModel->account_type;
        $this->approved_rejected_by = $childModel->approved_rejected_by;
        $this->approved_rejected_at = $childModel->approved_rejected_at;
        $this->rejected_reason = $childModel->rejected_reason;
        $this->rejected_reason_body = $childModel->rejected_reason_body;
        $this->created_at = $childModel->created_at;
        $this->updated_at = $childModel->updated_at;
        $this->data = (object) [
            'id' => $parentModel->id,
            'name' => $childModel->name,
            'description' => $childModel->description,
            'address_1' => $childModel->address_1,
            'address_2' => $childModel->address_2,
            'city' => $childModel->city,
            'state' => $childModel->state,
            'zip_code' => $childModel->zip_code,
            'country' => $childModel->country,
            'phone_number' => $childModel->phone_number,
            'full_time_permanent_employees' => $childModel->full_time_permanent_employees ?? null,
            'seasonal_employees' => $childModel->seasonal_employees ?? null,
            'part_time_permanent_employees' => $childModel->part_time_permanent_employees ?? null,
            'percentage_female' => $childModel->percentage_female ?? null,
            'percentage_youth' => $childModel->percentage_youth ?? null,
            'website' => $childModel->website,
            'key_contact' => $childModel->key_contact,
            'type' => $childModel->type,
            'category' => $childModel->category,
            'facebook' => $childModel->facebook,
            'twitter' => $childModel->twitter,
            'linkedin' => $childModel->linkedin,
            'instagram' => $childModel->instagram,
            'avatar' => $childModel->avatar,
            'cover_photo' => $childModel->cover_photo,
            'video' => $childModel->video,
            'community_engagement_strategy' => $childModel->community_engagement_strategy,
            'three_year_community_engagement' => $childModel->three_year_community_engagement,
            'women_farmer_engagement' => $childModel->women_farmer_engagement,
            'young_people_engagement' => $childModel->young_people_engagement,
            'monitoring_and_evaluation_experience' => $childModel->monitoring_and_evaluation_experience,
            'community_follow_up' => $childModel->community_follow_up,
            'total_hectares_restored' => $childModel->total_hectares_restored,
            'hectares_restored_three_years' => $childModel->hectares_restored_three_years,
            'total_trees_grown' => $childModel->total_trees_grown,
            'tree_survival_rate' => $childModel->tree_survival_rate,
            'tree_maintenance_and_aftercare' => $childModel->tree_maintenance_and_aftercare,
            'founded_at' => $childModel->founded_at,
            'revenues_19' => $childModel->revenues_19,
            'revenues_20' => $childModel->revenues_20,
            'revenues_21' => $childModel->revenues_21,
            'files' => $this->getFiles($parentModel),
            'photos' => $this->getPhotos($parentModel),
            'created_at' => $parentModel->created_at,
            'updated_at' => $childModel->created_at,
        ];
    }

    private function getFiles($organisation)
    {
        $resources = [];
        foreach ($organisation->organisationFiles as $organisationFile) {
            $resources[] = new OrganisationFileResource($organisationFile);
        }

        return $resources;
    }

    private function getPhotos($organisation)
    {
        $resources = [];
        foreach ($organisation->organisationPhotos as $organisationPhoto) {
            $resources[] = new OrganisationPhotoResource($organisationPhoto);
        }

        return $resources;
    }
}
