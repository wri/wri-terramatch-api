<?php

namespace App\Resources\Terrafund;

use App\Http\Resources\V2\BaselineMonitoring\ProjectMetricResource;
use App\Models\Terrafund\TerrafundProgramme as TerrafundProgrammeModel;
use App\Resources\Resource;

class TerrafundProgrammeResource extends Resource
{
    public function __construct(TerrafundProgrammeModel $programme)
    {
        $this->id = $programme->id;
        $this->name = $programme->name;
        $this->description = $programme->description;
        $this->planting_start_date = $programme->planting_start_date;
        $this->planting_end_date = $programme->planting_end_date;
        $this->budget = $programme->budget;
        $this->status = $programme->status;
        $this->project_country = $programme->project_country;
        $this->home_country = $programme->home_country;
        $this->boundary_geojson = $programme->boundary_geojson;
        $this->history = $programme->history;
        $this->objectives = $programme->objectives;
        $this->environmental_goals = $programme->environmental_goals;
        $this->socioeconomic_goals = $programme->socioeconomic_goals;
        $this->sdgs_impacted = $programme->sdgs_impacted;
        $this->long_term_growth = $programme->long_term_growth;
        $this->community_incentives = $programme->community_incentives;
        $this->total_hectares_restored = $programme->total_hectares_restored;
        $this->trees_planted = $programme->trees_planted;
        $this->jobs_created = $programme->jobs_created;
        $this->framework_id = $programme->framework_id;
        $this->organisation_id = $programme->organisation_id;
        $this->tree_species = $this->getTreeSpecies($programme);
        $this->additional_files = $this->getAdditionalFiles($programme);
        $this->baseline_monitoring = $this->getBaselineMonitoring($programme);
        $this->next_due_submission = $programme->next_due_submission;
        $this->next_due_at = $programme->next_due_submission ? $programme->next_due_submission->due_at : null;
        $this->created_at = $programme->created_at;
        $this->updated_at = $programme->updated_at;
    }

    private function getTreeSpecies($programme)
    {
        $resources = [];
        foreach ($programme->terrafundTreeSpecies as $terrafundTreeSpecies) {
            $resources[] = new TerrafundTreeSpeciesResource($terrafundTreeSpecies);
        }

        return $resources;
    }

    private function getAdditionalFiles($programme)
    {
        $resources = [];
        foreach ($programme->terrafundFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }

    private function getBaselineMonitoring($programme): ?ProjectMetricResource
    {
        $programmeMetric = $programme->baselineMonitoring->first();
        if (empty($programmeMetric)) {
            return null;
        }

        return new ProjectMetricResource($programmeMetric);
    }
}
