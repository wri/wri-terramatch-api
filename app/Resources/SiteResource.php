<?php

namespace App\Resources;

use App\Http\Resources\V2\BaselineMonitoring\SiteMetricResource;
use App\Models\Site as SiteModel;

class SiteResource extends Resource
{
    public function __construct(SiteModel $site)
    {
        $this->id = $site->id;
        $this->programme_id = $site->programme_id;
        $this->name_with_id = $site->name_with_id;
        $this->control_site = $site->control_site;
        $this->name = $site->name;
        $this->country = $site->programme->country;
        $this->continent = $site->programme->continent;
        $this->description = $site->description;
        $this->planting_pattern = $site->planting_pattern;
        $this->stratification_for_heterogeneity = $site->stratification_for_heterogeneity;
        $this->history = $site->history;
        $this->workdays_paid = $site->total_paid_workdays;
        $this->workdays_volunteer = $site->total_volunteer_workdays;
        $this->total_workdays = $site->total_workdays;
        $this->establishment_date = $site->establishment_date;
        $this->end_date = $site->end_date;
        $this->restoration_methods = $this->getRestorationMethods($site);
        $this->land_tenures = $this->getLandTenures($site);
        $this->seeds = $this->getSeedDetails($site);
        $this->invasives = $this->getInvasives($site);
        $this->technical_narrative = $site->technical_narrative;
        $this->public_narrative = $site->public_narrative;
        $this->aim_survival_rate = $site->aim_survival_rate;
        $this->aim_year_five_crown_cover = $site->aim_year_five_crown_cover;
        $this->aim_direct_seeding_survival_rate = $site->aim_direct_seeding_survival_rate;
        $this->aim_natural_regeneration_trees_per_hectare = $site->aim_natural_regeneration_trees_per_hectare;
        $this->aim_natural_regeneration_hectares = $site->aim_natural_regeneration_hectares;
        $this->aim_soil_condition = $site->aim_soil_condition;
        $this->aim_number_of_mature_trees = $site->aim_number_of_mature_trees;
        $this->boundary_geojson = $site->boundary_geojson;
        $this->submissions = $this->getSubmissions($site);
        $this->document_files = $this->getDocumentFiles($site);
        $this->baseline_monitoring = $this->getBaselineMonitoring($site);
        $this->media = $this->getMedia($site);
        $this->additional_tree_species = $this->getAdditionalTreeSpecies($site);
        $this->next_due_submission_id = $site->next_due_submission ? $site->next_due_submission->id : null;
        $this->next_due_at = $site->next_due_submission ? $site->next_due_submission->due_at : null;
        $this->created_at = $site->created_at;
        $this->updated_at = $site->updated_at;
    }

    private function getLandTenures(SiteModel $site)
    {
        $landTenureResources = [];
        foreach ($site->landTenures as $landTenure) {
            $landTenureResources[] = new LandTenureResource($landTenure);
        }

        return $landTenureResources;
    }

    private function getRestorationMethods(SiteModel $site)
    {
        $restorationMethodResources = [];
        foreach ($site->siteRestorationMethods as $siteRestorationMethod) {
            $restorationMethodResources[] = new SiteRestorationMethodResource($siteRestorationMethod);
        }

        return $restorationMethodResources;
    }

    private function getSubmissions(SiteModel $site)
    {
        $submissionResources = [];
        foreach ($site->submissions as $submission) {
            $submissionResources[] = new SiteSubmissionResource($submission);
        }

        return $submissionResources;
    }

    private function getSeedDetails(SiteModel $site)
    {
        $seedDetailResources = [];
        foreach ($site->seedDetails as $seedDetail) {
            $seedDetailResources[] = new SeedDetailResource($seedDetail);
        }

        return $seedDetailResources;
    }

    private function getInvasives(SiteModel $site)
    {
        $invasiveResources = [];
        foreach ($site->invasives as $invasive) {
            $invasiveResources[] = new InvasiveResource($invasive);
        }

        return $invasiveResources;
    }

    private function getMedia(SiteModel $site)
    {
        $resources = [];
        foreach ($site->media as $media) {
            $resources[] = new MediaUploadResource($media);
        }

        return $resources;
    }

    private function getDocumentFiles(SiteModel $site)
    {
        $resources = [];
        foreach ($site->getDocumentFileExcludingCollection(['tree_species']) as $documentFile) {
            $resources[] = new DocumentFileResource($documentFile);
        }

        return $resources;
    }

    private function getAdditionalTreeSpecies(SiteModel $site): ?DocumentFileLightResource
    {
        $treeSpeciesFile = $site->getDocumentFileCollection(['tree_species'])->first();

        if (empty($treeSpeciesFile)) {
            return null;
        }

        return new DocumentFileLightResource($treeSpeciesFile);
    }

    private function getBaselineMonitoring(SiteModel $site): ?SiteMetricResource
    {
        $siteMetric = $site->baselineMonitoring->first();
        if (empty($siteMetric)) {
            return null;
        }

        return new SiteMetricResource($siteMetric);
    }
}
