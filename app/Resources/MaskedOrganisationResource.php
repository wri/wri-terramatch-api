<?php

namespace App\Resources;

use App\Models\Organisation as ParentModel;
use App\Models\OrganisationVersion as ChildModel;

class MaskedOrganisationResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->name = $this->handleValue($parentModel, $childModel, 'name');
        $this->description = $this->handleValue($parentModel, $childModel, 'description');
        $this->city = $this->handleValue($parentModel, $childModel, 'city', 'hq_city');
        $this->state = $this->handleValue($parentModel, $childModel, 'state', 'hq_state');
        $this->country = $this->handleValue($parentModel, $childModel, 'country', 'hq_country');
        $this->website = $this->handleValue($parentModel, $childModel, 'website', 'web_url');
        $this->key_contact = $this->handleValue($parentModel, $childModel, 'key_contact');
        $this->type = $this->handleValue($parentModel, $childModel, 'type');
        $this->category = $this->handleValue($parentModel, $childModel, 'category');
        $this->facebook = $this->handleValue($parentModel, $childModel, 'facebook', 'facebook_url');
        $this->twitter = $this->handleValue($parentModel, $childModel, 'twitter', 'twitter_url');
        $this->linkedin = $this->handleValue($parentModel, $childModel, 'linkedin', 'linkedin_url');
        $this->instagram = $this->handleValue($parentModel, $childModel, 'instagram', 'instagram_url');
        $this->avatar = $this->handleValue($parentModel, $childModel, 'avatar');
        $this->cover_photo = $this->handleValue($parentModel, $childModel, 'cover_photo');
        $this->video = $this->handleValue($parentModel, $childModel, 'video');
        $this->full_time_permanent_employees = $this->handleValue($parentModel, $childModel, 'full_time_permanent_employees', 'ft_permanent_employees');
        $this->seasonal_employees = $this->handleValue($parentModel, $childModel, 'seasonal_employees');
        $this->part_time_permanent_employees = $this->handleValue($parentModel, $childModel, 'part_time_permanent_employees', 'pt_permanent_employees');
        $this->percentage_female = $this->handleValue($parentModel, $childModel, 'percentage_female', 'percent_engaged_women_3yr');
        $this->percentage_youth = $this->handleValue($parentModel, $childModel, 'percentage_youth', 'percent_engaged_under_35_3yr');
        $this->community_engagement_strategy = $this->handleValue($parentModel, $childModel, 'community_engagement_strategy');
        $this->three_year_community_engagement = $this->handleValue($parentModel, $childModel, 'three_year_community_engagement');
        $this->women_farmer_engagement = $this->handleValue($parentModel, $childModel, 'women_farmer_engagement');
        $this->young_people_engagement = $this->handleValue($parentModel, $childModel, 'young_people_engagement', 'engagement_youth');
        $this->monitoring_and_evaluation_experience = $this->handleValue($parentModel, $childModel, 'monitoring_and_evaluation_experience', 'monitoring_evaluation_experience');
        $this->community_follow_up = $this->handleValue($parentModel, $childModel, 'community_follow_up');
        $this->total_hectares_restored = $this->handleValue($parentModel, $childModel, 'total_hectares_restored', 'ha_restored_total');
        $this->hectares_restored_three_years = $this->handleValue($parentModel, $childModel, 'hectares_restored_three_years', 'ha_restored_3year');
        $this->total_trees_grown = $this->handleValue($parentModel, $childModel, 'total_trees_grown', 'trees_grown_total');
        $this->tree_survival_rate = $this->handleValue($parentModel, $childModel, 'tree_survival_rate');
        $this->tree_maintenance_and_aftercare = $this->handleValue($parentModel, $childModel, 'tree_maintenance_and_aftercare', 'tree_maintenance_aftercare_approach');
        $this->founded_at = $this->handleValue($parentModel, $childModel, 'founded_at', 'founding_date');
        $this->revenues_19 = $this->handleValue($parentModel, $childModel, 'revenues_19');
        $this->revenues_20 = $this->handleValue($parentModel, $childModel, 'revenues_20');
        $this->revenues_21 = $this->handleValue($parentModel, $childModel, 'revenues_21');
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $this->handleValue($parentModel, $childModel, 'created_at', 'updated_at');
    }

    private function handleValue($parent, $child, string $childProperty, ?string $parentProperty = null)
    {
        $parentProperty = $parentProperty ?? $childProperty;

        return empty($parent->$parentProperty) ? data_get($child, $childProperty) : data_get($parent, $parentProperty);
    }

    private function handleAvatar($parent, $child, string $childProperty): ?string
    {
        if (! empty($child->avatar)) {
            return $child->avatar;
        }

        $logo = $parent->getMedia('logo')->first();

        return ! empty($logo) ? $logo->getFullUrl() : null;
    }
}
