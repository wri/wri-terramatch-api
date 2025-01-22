<?php

namespace App\Http\Resources\V2\Sites;

use Illuminate\Http\Resources\Json\JsonResource;

class V2SiteResource extends JsonResource
{
    /**
     * @todo relations will need to be added
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'terrafund_site_id' => $this->terrafund_site_id,
            'programme_id' => $this->programme_id,
            'control_site' => $this->control_site,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'project_name' => $this->project_name ?? null,
            'framework_key' => $this->framework_key ?? null,
            'monitored_data' => $this->monitoring ?? null,
            'country' => $this->programme ? $this->programme->country : null,
            'project_country' => $this->terrafundProgramme ? $this->terrafundProgramme->project_country : null,
            'continent' => $this->programme ? $this->programme->continent : null,
            'description' => $this->description,
            'planting_pattern' => $this->planting_pattern,
            'stratification_for_heterogeneity' => $this->stratification_for_heterogeneity,
            'history' => $this->history,
            'workdays_paid' => $this->total_paid_workdays,
            'workdays_volunteer' => $this->total_volunteer_workdays,
            'establishment_date' => $this->establishment_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'technical_narrative' => $this->technical_narrative,
            'public_narrative' => $this->public_narrative,
            'aim_survival_rate' => $this->aim_survival_rate,
            'aim_year_five_crown_cover' => $this->aim_year_five_crown_cover,
            'aim_direct_seeding_survival_rate' => $this->aim_direct_seeding_survival_rate,
            'aim_natural_regeneration_trees_per_hectare' => $this->aim_natural_regeneration_trees_per_hectare,
            'aim_natural_regeneration_hectares' => $this->aim_natural_regeneration_hectares,
            'aim_soil_condition' => $this->aim_soil_condition,
            'aim_number_of_mature_trees' => $this->aim_number_of_mature_trees,
            'hectares_to_restore' => $this->hectares_to_restore,
            'landscape_community_contribution' => $this->landscape_community_contribution,
            'disturbances' => $this->disturbances,
            'boundary_geojson' => $this->boundary_geojson,
            // 'restoration_methods' => $this->getRestorationMethods(),
            // 'land_tenures' => $this->getLandTenures(),
            // 'seeds' => $this->getSeedDetails(),
            // 'invasives' => $this->getInvasives(),
            // 'submissions' => $this->getSubmissions(),
            // 'document_files' => $this->getDocumentFiles(),
            // 'baseline_monitoring' => $this->getBaselineMonitoring(),
            // 'media' => $this->getMedia(),
            // 'additional_tree_species' => $this->getAdditionalTreeSpecies(),
            // 'photos' => $this->getPhotos(),
            // 'baseline_monitoring' => $this->getBaselineMonitoring(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
