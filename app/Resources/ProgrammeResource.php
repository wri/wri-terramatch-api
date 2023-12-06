<?php

namespace App\Resources;

use App\Http\Resources\V2\BaselineMonitoring\ProjectMetricResource;
use App\Models\Programme as ProgrammeModel;

class ProgrammeResource extends Resource
{
    public function __construct(ProgrammeModel $programme, $submissions = null, $numberOfSites = null)
    {
        $this->id = $programme->id;
        $this->name = $programme->name;
        $this->country = $programme->country;
        $this->continent = $programme->continent;
        $this->thumbnail = $programme->thumbnail;
        $this->end_date = $programme->end_date;
        $this->framework_id = $programme->framework_id;
        $this->organisation_id = $programme->organisation_id;
        $this->boundary_geojson = $programme->boundary_geojson;
        $this->number_of_sites = $numberOfSites;
        $this->workdays_paid = $programme->total_paid_workdays;
        $this->workdays_volunteer = $programme->total_volunteer_workdays;
        $this->total_workdays = $programme->total_workdays;
        $this->next_due_submission_id = $programme->next_due_submission ? $programme->next_due_submission->id : null;

        $this->next_due_at = $programme->next_due_submission ? $programme->next_due_submission->due_at : null;
        $this->created_at = $programme->created_at;
        $this->updated_at = $programme->updated_at;
        $this->document_files = $this->getDocumentFiles($programme);
        $this->additional_tree_species = $this->getAdditionalTreeSpecies($programme);

        $this->submissions = $submissions ?: $this->getSubmissions($programme);
        $this->baseline_monitoring = $this->getBaselineMonitoring($programme);
    }

    private function getSubmissions($programme): array
    {
        $submissionResources = [];
        foreach ($programme->submissions as $submission) {
            $submissionResources[] = new SubmissionResource($submission);
        }

        return $submissionResources;
    }

    private function getDocumentFiles(ProgrammeModel $programme): array
    {
        $resources = [];
        foreach ($programme->getDocumentFileExcludingCollection(['tree_species']) as $documentFile) {
            $resources[] = new DocumentFileResource($documentFile);
        }

        return $resources;
    }

    private function getAdditionalTreeSpecies(ProgrammeModel $programme): ?DocumentFileLightResource
    {
        $treeSpeciesFile = $programme->getDocumentFileCollection(['tree_species'])->first();

        if (empty($treeSpeciesFile)) {
            return null;
        }

        return new DocumentFileLightResource($treeSpeciesFile);
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
