<?php

namespace App\Resources;

use App\Models\Submission;
use App\Models\Submission as SubmissionModel;

class SubmissionResource extends Resource
{
    public function __construct(SubmissionModel $submission)
    {
        $this->id = $submission->id;
        $this->programme_id = $submission->programme_id;
        $this->title = $submission->title;
        $this->technical_narrative = $submission->technical_narrative;
        $this->public_narrative = $submission->public_narrative;
        $this->workdays_paid = $submission->workdays_paid;
        $this->total_workdays = $submission->total_workdays;
        $this->workdays_volunteer = $submission->workdays_volunteer;
        $this->media = $this->getMedia($submission);
        $this->tree_species = $this->getTreeSpecies($submission);
        $this->socioeconomic_benefits = $this->getSocioeconomicBenefits($submission);
        $this->document_files = $this->getDocumentFiles($submission);
        $this->additional_tree_species = $this->getAdditionalTreeSpecies($submission);
        $this->status = $submission->approved_at ? Submission::STATUS_APPROVED : Submission::STATUS_AWAITING_APPROVAL;
        $this->due_date = empty($submission->dueSubmission->due_at) ? null : $submission->dueSubmission->due_at;
        $this->created_by = $submission->created_by;
        $this->created_at = $submission->created_at;
        $this->updated_at = $submission->updated_at;
    }

    private function getTreeSpecies(SubmissionModel $submission)
    {
        $resources = [];
        foreach ($submission->programmeTreeSpecies as $programmeTreeSpecies) {
            $resources[] = new ProgrammeTreeSpeciesResource($programmeTreeSpecies);
        }

        return $resources;
    }

    private function getMedia(SubmissionModel $submission)
    {
        $resources = [];
        foreach ($submission->mediaUploads as $media) {
            $resources[] = new SubmissionMediaUploadResource($media);
        }

        return $resources;
    }

    private function getSocioeconomicBenefits(SubmissionModel $submission)
    {
        if ($submission->socioeconomicBenefits) {
            return new SocioeconomicBenefitResource($submission->socioeconomicBenefits);
        }

        return null;
    }

    private function getDocumentFiles(SubmissionModel $submission)
    {
        $resources = [];
        foreach ($submission->documentFiles as $documentFile) {
            $resources[] = new DocumentFileResource($documentFile);
        }

        return $resources;
    }

    private function getAdditionalTreeSpecies(SubmissionModel $submission): ?DocumentFileLightResource
    {
        $treeSpeciesFile = $submission->getDocumentFileCollection(['tree_species'])->first();

        if (empty($treeSpeciesFile)) {
            return null;
        }

        return new DocumentFileLightResource($treeSpeciesFile);
    }
}
