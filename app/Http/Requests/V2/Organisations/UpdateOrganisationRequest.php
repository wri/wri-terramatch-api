<?php

namespace App\Http\Requests\V2\Organisations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'type' => 'sometimes|nullable|string',
            'subtype' => 'sometimes|nullable|string',
            'private' => 'sometimes|boolean',
            'name' => 'sometimes|nullable|string',
            'phone' => 'sometimes|nullable|string',
            'hq_address' => 'sometimes|nullable|string',
            'founding_date' => 'sometimes|nullable|date',
            'description' => 'sometimes|nullable|string',

            'countries' => 'sometimes|nullable|array',
            'languages' => 'sometimes|nullable|array',
            'engagement_farmers' => 'sometimes|nullable|array',
            'engagement_women' => 'sometimes|nullable|array',
            'engagement_youth' => 'sometimes|nullable|array',

            'web_url' => 'sometimes|nullable|string',
            'facebook_url' => 'sometimes|nullable|string',
            'instagram_url' => 'sometimes|nullable|string',
            'linkedin_url' => 'sometimes|nullable|string',
            'twitter_url' => 'sometimes|nullable|string',

            'hq_street_1' => 'sometimes|nullable|string',
            'hq_street_2' => 'sometimes|nullable|string',
            'hq_city' => 'sometimes|nullable|string',
            'hq_state' => 'sometimes|nullable|string',
            'hq_zipcode' => 'sometimes|nullable|string',
            'hq_country' => 'sometimes|nullable|string',

            'fin_start_month' => 'sometimes|nullable|integer',
            'fin_budget_3year' => 'sometimes|nullable|numeric|between:0,999999999999',
            'fin_budget_2year' => 'sometimes|nullable|numeric|between:0,999999999999',
            'fin_budget_1year' => 'sometimes|nullable|numeric|between:0,999999999999',
            'fin_budget_current_year' => 'sometimes|nullable|numeric|between:0,999999999999',

            'ha_restored_total' => 'sometimes|nullable|numeric|min:0',
            'ha_restored_3year' => 'sometimes|nullable|numeric|min:0',
            'relevant_experience_years' => 'sometimes|nullable|integer|between:0,150',

            'trees_grown_total' => 'sometimes|nullable|integer|min:0',
            'trees_grown_3year' => 'sometimes|nullable|integer|min:0',
            'tree_care_approach' => 'sometimes|nullable|string',
            'tags' => 'sometimes|nullable|array',

            'ft_permanent_employees' => 'sometimes|nullable|numeric|min:0',
            'pt_permanent_employees' => 'sometimes|nullable|numeric|min:0',
            'temp_employees' => 'sometimes|nullable|numeric|min:0',
            'total_employees' => 'sometimes|nullable|numeric|min:0',
            'female_employees' => 'sometimes|nullable|numeric|min:0',
            'male_employees' => 'sometimes|nullable|numeric|min:0',
            'young_employees' => 'sometimes|nullable|numeric|min:0',
            'additional_funding_details' => 'sometimes|nullable|string',
            'community_experience' => 'sometimes|nullable|string',
            'engagement_non_youth' => 'sometimes|nullable|array',
            'tree_restoration_practices' => 'sometimes|nullable|array',
            'organisation_revenue_this_year' => 'sometimes|nullable|numeric',
            'business_model' => 'sometimes|nullable|string',
            'total_engaged_community_members_3yr' => 'sometimes|nullable|numeric|min:0',
            'percent_engaged_women_3yr' => 'sometimes|nullable|numeric|min:0|max:100',
            'percent_engaged_men_3yr' => 'sometimes|nullable|numeric|min:0|max:100',
            'percent_engaged_under_35_3yr' => 'sometimes|nullable|numeric|min:0|max:100',
            'percent_engaged_over_35_3yr' => 'sometimes|nullable|numeric|min:0|max:100',
            'percent_engaged_smallholder_3yr' => 'sometimes|nullable|numeric|min:0|max:100',
            'total_trees_grown' => 'sometimes|nullable|numeric|min:0',
            'avg_tree_survival_rate' => 'sometimes|nullable|numeric|min:0|max:100',
            'tree_maintenance_aftercare_approach' => 'sometimes|nullable|string',
            'restored_areas_description' => 'sometimes|nullable|string',
            'monitoring_evaluation_experience' => 'sometimes|nullable|string',
            'funding_history' => 'sometimes|nullable|array',
            'shapefiles' => 'sometimes|nullable|array',
            'states' => 'sometimes|nullable|array',
            'district' => 'sometimes|nullable|string',
            'account_number_1' => 'sometimes|nullable|string',
            'account_number_2' => 'sometimes|nullable|string',
            'loan_status_amount' => 'sometimes|nullable|string',
            'loan_status_types' => 'sometimes|nullable|array',
            'approach_of_marginalized_communities' => 'sometimes|nullable|string',
            'community_engagement_numbers_marginalized' => 'sometimes|nullable|string',
            'land_systems' => 'sometimes|nullable|array',
            'fund_utilisation' => 'sometimes|nullable|array',
            'community_members_engaged_3yr' => 'sometimes|nullable|numeric',
            'community_members_engaged_3yr_women' => 'sometimes|nullable|numeric',
            'community_members_engaged_3yr_men' => 'sometimes|nullable|numeric',
            'community_members_engaged_3yr_youth' => 'sometimes|nullable|numeric',
            'community_members_engaged_3yr_non_youth' => 'sometimes|nullable|numeric',
            'community_members_engaged_3yr_smallholder' => 'sometimes|nullable|numeric',
            'community_members_engaged_3yr_backward_class' => 'sometimes|nullable|numeric',
            'environmental_impact' => 'sometimes|nullable|string',
            'socioeconomic_impact' => 'sometimes|nullable|string',
            'growith_stage' => 'sometimes|nullable|string',

            'restoration_types_implemented' => 'sometimes|nullable|array',
            'historic_monitoring_geojson' => 'sometimes|nullable|string',
        ];
    }
}
