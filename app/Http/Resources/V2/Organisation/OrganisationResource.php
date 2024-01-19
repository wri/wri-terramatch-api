<?php

namespace App\Http\Resources\V2\Organisation;

use App\Http\Resources\V2\CoreTeamLeaderResource;
use App\Http\Resources\V2\FundingTypeResource;
use App\Http\Resources\V2\General\ShapefileResource;
use App\Http\Resources\V2\LeadershipTeamResource;
use App\Http\Resources\V2\OwnershipStakeResource;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchResource;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'type' => $this->type,
            'private' => $this->private,

            'name' => $this->name,
            'phone' => $this->phone,
            'currency' => $this->currency,
            'hq_street_1' => $this->hq_street_1,
            'hq_street_2' => $this->hq_street_2,
            'hq_city' => $this->hq_city,
            'hq_state' => $this->hq_state,
            'hq_zipcode' => $this->hq_zipcode,
            'hq_country' => $this->hq_country,

            'countries' => $this->countries,
            'languages' => $this->languages,

            'founding_date' => $this->founding_date,
            'description' => $this->description,

            'tree_species' => TreeSpeciesResource::collection($this->treeSpecies),
            'tree_species_restored' => TreeSpeciesResource::collection($this->treeSpeciesRestored),
            'project_pitches' => ProjectPitchResource::collection($this->projectPitches),
            'leadership_team' => LeadershipTeamResource::collection($this->leadershipTeam),
            'core_team_leaders' => CoreTeamLeaderResource::collection($this->coreTeamLeaders),
            'funding_types' => FundingTypeResource::collection($this->fundingTypes),
            'ownership_stake' => OwnershipStakeResource::collection($this->ownershipStake),

            'leadership_team_txt' => $this->leadership_team_txt,
            'web_url' => $this->web_url,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'linkedin_url' => $this->linkedin_url,
            'twitter_url' => $this->twitter_url,

            'fin_start_month' => $this->fin_start_month,
            'fin_budget_3year' => $this->fin_budget_3year,
            'fin_budget_2year' => $this->fin_budget_2year,
            'fin_budget_1year' => $this->fin_budget_1year,
            'fin_budget_current_year' => $this->fin_budget_current_year,

            'ha_restored_total' => $this->ha_restored_total,
            'ha_restored_3year' => $this->ha_restored_3year,
            'trees_grown_total' => $this->trees_grown_total,
            'trees_grown_3year' => $this->trees_grown_3year,
            'tree_care_approach' => $this->tree_care_approach,
            'relevant_experience_years' => $this->relevant_experience_years,

            'ft_permanent_employees' => $this->ft_permanent_employees,
            'pt_permanent_employees' => $this->pt_permanent_employees,
            'temp_employees' => $this->temp_employees,
            'total_employees' => $this->total_employees,
            'female_employees' => $this->female_employees,
            'male_employees' => $this->male_employees,
            'young_employees' => $this->young_employees,
            'over_35_employees' => $this->over_35_employees,
            'additional_funding_details' => $this->additional_funding_details,
            'community_experience' => $this->community_experience,
            'engagement_non_youth' => $this->engagement_non_youth,
            'tree_restoration_practices' => $this->tree_restoration_practices,
            'business_model' => $this->business_model,
            'subtype' => $this->subtype,
            'organisation_revenue_this_year' => $this->organisation_revenue_this_year,
            'total_engaged_community_members_3yr' => $this->total_engaged_community_members_3yr,
            'percent_engaged_women_3yr' => $this->percent_engaged_women_3yr,
            'percent_engaged_men_3yr' => $this->percent_engaged_men_3yr,
            'percent_engaged_under_35_3yr' => $this->percent_engaged_under_35_3yr,
            'percent_engaged_over_35_3yr' => $this->percent_engaged_over_35_3yr,
            'percent_engaged_smallholder_3yr' => $this->percent_engaged_smallholder_3yr,
            'total_trees_grown' => $this->total_trees_grown,
            'avg_tree_survival_rate' => $this->avg_tree_survival_rate,
            'tree_maintenance_aftercare_approach' => $this->tree_maintenance_aftercare_approach,
            'restored_areas_description' => $this->restored_areas_description,
            'monitoring_evaluation_experience' => $this->monitoring_evaluation_experience,
            'funding_history' => $this->funding_history,
            'shapefiles' => ShapefileResource::collection($this->shapefiles),
            'engagement_farmers' => $this->engagement_farmers,
            'engagement_women' => $this->engagement_women,
            'engagement_youth' => $this->engagement_youth,

            'restoration_types_implemented' => $this->restoration_types_implemented,
            'seedlings_source' => $this->seedlings_source,
            'historic_monitoring_geojson' => $this->historic_monitoring_geojson,

            'states' => $this->states,
            'district' => $this->district,
            'account_number_1' => $this->account_number_1,
            'account_number_2' => $this->account_number_2,
            'loan_status_amount' => $this->loan_status_amount,
            'loan_status_types' => $this->loan_status_types,
            'approach_of_marginalized_communities' => $this->approach_of_marginalized_communities,
            'community_engagement_numbers_marginalized' => $this->community_engagement_numbers_marginalized,
            'land_systems' => $this->land_systems,
            'fund_utilisation' => $this->fund_utilisation,
            'detailed_intervention_types' => $this->detailed_intervention_types,
            'community_members_engaged_3yr' => $this->community_members_engaged_3yr,
            'community_members_engaged_3yr_women' => $this->community_members_engaged_3yr_women,
            'community_members_engaged_3yr_men' => $this->community_members_engaged_3yr_men,
            'community_members_engaged_3yr_youth' => $this->community_members_engaged_3yr_youth,
            'community_members_engaged_3yr_non_youth' => $this->community_members_engaged_3yr_non_youth,
            'community_members_engaged_3yr_smallholder' => $this->community_members_engaged_3yr_smallholder,
            'community_members_engaged_3yr_backward_class' => $this->community_members_engaged_3yr_backward_class,

            'total_board_members' => $this->total_board_members,
            'pct_board_women' => $this->pct_board_women,
            'pct_board_men' => $this->pct_board_men,
            'pct_board_youth' => $this->pct_board_youth,
            'pct_board_non_youth' => $this->pct_board_non_youth,

            'field_staff_skills' => $this->field_staff_skills,
            'fpc_company' => $this->fpc_company,
            'num_of_farmers_on_board' => $this->num_of_farmers_on_board,
            'num_of_marginalised_employees' => $this->num_of_marginalised_employees,
            'benefactors_fpc_company' => $this->benefactors_fpc_company,
            'board_remuneration_fpc_company' => $this->board_remuneration_fpc_company,
            'board_engagement_fpc_company' => $this->board_engagement_fpc_company,
            'biodiversity_focus' => $this->biodiversity_focus,
            'global_planning_frameworks' => $this->global_planning_frameworks,
            'past_gov_collaboration' => $this->past_gov_collaboration,
            'engagement_landless' => $this->past_gov_collaboration,
            'environmental_impact' => $this->environmental_impact,
            'socioeconomic_impact' => $this->socioeconomic_impact,
            'growith_stage' => $this->growith_stage,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'tags' => $this->buildTagList(),
        ];

        return $this->appendFilesToResource($data);
    }

    private function buildTagList(): array
    {
        $list = [];
        foreach ($this->tags as $tag) {
            $list[$tag->slug] = $tag->name ;
        }

        return $list;
    }
}
