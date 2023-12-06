<?php

namespace App\Resources;

use App\Models\SiteSubmission as SiteSubmissionModel;

class SiteSubmissionResource extends Resource
{
    public function __construct(SiteSubmissionModel $siteSubmission)
    {
        $this->id = $siteSubmission->id;
        $this->site_submission_title = $siteSubmission->site_submission_title;
        $this->site_id = $siteSubmission->site_id;
        $this->programme = $siteSubmission->site->programme_id;
        $this->disturbance_information = $siteSubmission->disturbance_information;
        $this->technical_narrative = $siteSubmission->technical_narrative;
        $this->public_narrative = $siteSubmission->public_narrative;
        $this->direct_seeding_kg = $siteSubmission->direct_seeding_kg;
        $this->direct_seeding = $this->getDirectSeedings($siteSubmission);
        $this->tree_species = $this->getTreeSpecies($siteSubmission);
        $this->disturbances = $this->getDisturbances($siteSubmission);
        $this->socioeconomic_benefits = $this->getSocioeconomicBenefits($siteSubmission);
        $this->media = $this->getMedia($siteSubmission);
        $this->document_files = $this->getDocumentFiles($siteSubmission);
        $this->additional_tree_species = $this->getAdditionalTreeSpecies($siteSubmission);
        $this->workdays_paid = $siteSubmission->workdays_paid;
        $this->total_workdays = $siteSubmission->total_workdays;
        $this->workdays_volunteer = $siteSubmission->workdays_volunteer;
        $this->due_date = empty($siteSubmission->dueSubmission->due_at) ? null : $siteSubmission->dueSubmission->due_at;
        $this->status = $siteSubmission->approved_at ? SiteSubmissionModel::STATUS_APPROVED : SiteSubmissionModel::STATUS_AWAITING_APPROVAL;
        $this->created_by = $siteSubmission->created_by;
        $this->created_at = $siteSubmission->created_at;
        $this->updated_at = $siteSubmission->updated_at;
    }

    private function getTreeSpecies(SiteSubmissionModel $siteSubmission)
    {
        $resources = [];
        foreach ($siteSubmission->siteTreeSpecies as $siteTreeSpecies) {
            $resources[] = new SiteTreeSpeciesResource($siteTreeSpecies);
        }

        return $resources;
    }

    private function getDirectSeedings(SiteSubmissionModel $siteSubmission)
    {
        $resources = [];
        foreach ($siteSubmission->directSeedings as $directSeeding) {
            $resources[] = new DirectSeedingResource($directSeeding);
        }

        return $resources;
    }

    private function getDisturbances(SiteSubmissionModel $siteSubmission)
    {
        $resources = [];
        foreach ($siteSubmission->disturbances as $disturbance) {
            $resources[] = new SiteSubmissionDisturbanceResource($disturbance);
        }

        return $resources;
    }

    private function getSocioeconomicBenefits(SiteSubmissionModel $siteSubmission)
    {
        if ($siteSubmission->socioeconomicBenefits) {
            return new SocioeconomicBenefitResource($siteSubmission->socioeconomicBenefits);
        }

        return null;
    }

    private function getMedia(SiteSubmissionModel $siteSubmission)
    {
        $resources = [];
        foreach ($siteSubmission->mediaUploads as $media) {
            $resources[] = new SubmissionMediaUploadResource($media);
        }

        return $resources;
    }

    private function getDocumentFiles(SiteSubmissionModel $siteSubmission)
    {
        $resources = [];
        foreach ($siteSubmission->documentFiles as $documentFile) {
            $resources[] = new DocumentFileResource($documentFile);
        }

        return $resources;
    }

    private function getAdditionalTreeSpecies(SiteSubmissionModel $siteSubmission): ?DocumentFileLightResource
    {
        $treeSpeciesFile = $siteSubmission->getDocumentFileCollection(['tree_species'])->first();

        if (empty($treeSpeciesFile)) {
            return null;
        }

        return new DocumentFileLightResource($treeSpeciesFile);
    }
}
