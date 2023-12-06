<?php

namespace App\Resources;

use App\Models\V2\Organisation;

class OrganisationV2ToV1Resource extends Resource
{
    public function __construct(Organisation $organisation)
    {
        $this->data = (object) [
            $this->id = $organisation->id ,
            $this->name = $organisation->name,
            $this->description = $organisation->description,
            $this->address_1 = $organisation->hq_street_1,
            $this->address_2 = $organisation->hq_street_2,
            $this->account_type = null,
            $this->city = $organisation->hq_city,
            $this->state = $organisation->hq_state,
            $this->zip_code = $organisation->hq_zipcode,
            $this->country = $organisation->hq_country,
            $this->phone_number = $organisation->phone,
            $this->website = $organisation->web_url,
            $this->key_contact = null,
            $this->type = $organisation->type,
            $this->category = 'developer',
            $this->facebook = $organisation->facebook_url,
            $this->twitter = $organisation->twitter_url,
            $this->linkedin = $organisation->linkedin_url,
            $this->instagram = $organisation->instagram_url,
            $this->avatar = null,
            $this->cover_photo = null,
            $this->video = null,
            $this->full_time_permanent_employees = null,
            $this->seasonal_employees = null,
            $this->part_time_permanent_employees = null,
            $this->percentage_female = null,
            $this->percentage_youth = null,
            $this->community_engagement_strategy = null,
            $this->three_year_community_engagement = null,
            $this->women_farmer_engagement = null,
            $this->young_people_engagement = null,
            $this->monitoring_and_evaluation_experience = null,
            $this->total_hectares_restored = null,
            $this->hectares_restored_three_years = null,
            $this->total_trees_grown = null,
            $this->tree_survival_rate = null,
            $this->tree_maintenance_and_aftercare = null,
            $this->community_follow_up = null,
            $this->founded_at = null,
            $this->files = null,
            $this->photos = null,
            $this->created_at = $organisation->created_at,
            $this->updated_at = $organisation->updated_at,
        ];
    }
}
