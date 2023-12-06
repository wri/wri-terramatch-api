<?php

namespace App\Resources;

use App\Models\Organisation as ParentModel;
use App\Models\OrganisationVersion as ChildModel;

class OrganisationResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id ?? null;
        $this->name = $childModel->name ?? null;
        $this->description = $childModel->description ?? null;
        $this->address_1 = $childModel->address_1 ?? null;
        $this->address_2 = $childModel->address_2 ?? null;
        $this->account_type = $childModel->account_type ?? null;
        $this->city = $childModel->city ?? null;
        $this->state = $childModel->state ?? null;
        $this->zip_code = $childModel->zip_code ?? null;
        $this->country = $childModel->country ?? null;
        $this->phone_number = $childModel->phone_number ?? null;
        $this->website = $childModel->website ?? null;
        $this->key_contact = $childModel->key_contact ?? null;
        $this->account_type = $childModel->account_type ?? null;
        $this->type = $childModel->type ?? null;
        $this->category = $childModel->category ?? null;
        $this->facebook = $childModel->facebook ?? null;
        $this->twitter = $childModel->twitter ?? null;
        $this->linkedin = $childModel->linkedin ?? null;
        $this->instagram = $childModel->instagram ?? null;
        $this->avatar = $childModel->avatar ?? null;
        $this->cover_photo = $childModel->cover_photo ?? null;
        $this->video = $childModel->video ?? null;
        $this->full_time_permanent_employees = $childModel->full_time_permanent_employees ?? null;
        $this->seasonal_employees = $childModel->seasonal_employees ?? null;
        $this->part_time_permanent_employees = $childModel->part_time_permanent_employees ?? null;
        $this->percentage_female = $childModel->percentage_female ?? null;
        $this->percentage_youth = $childModel->percentage_youth ?? null;
        $this->community_engagement_strategy = $childModel->community_engagement_strategy ?? null;
        $this->three_year_community_engagement = $childModel->three_year_community_engagement ?? null;
        $this->women_farmer_engagement = $childModel->women_farmer_engagement ?? null;
        $this->young_people_engagement = $childModel->young_people_engagement ?? null;
        $this->monitoring_and_evaluation_experience = $childModel->monitoring_and_evaluation_experience ?? null;
        $this->total_hectares_restored = $childModel->total_hectares_restored ?? null;
        $this->hectares_restored_three_years = $childModel->hectares_restored_three_years ?? null;
        $this->total_trees_grown = $childModel->total_trees_grown ?? null;
        $this->tree_survival_rate = $childModel->tree_survival_rate ?? null;
        $this->tree_maintenance_and_aftercare = $childModel->tree_maintenance_and_aftercare ?? null;
        $this->community_follow_up = $childModel->community_follow_up ?? null;
        $this->founded_at = $childModel->founded_at ?? null;
        $this->files = $this->getFiles($parentModel);
        $this->photos = $this->getPhotos($parentModel);
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
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
