<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundSiteSubmission as TerrafundSiteSubmissionModel;
use App\Resources\Resource;

class TerrafundSiteSubmissionResource extends Resource
{
    public function __construct(TerrafundSiteSubmissionModel $terrafundSiteSubmission)
    {
        $this->id = $terrafundSiteSubmission->id;
        $this->photos = $this->getPhotos($terrafundSiteSubmission);
        $this->disturbances = $this->getDisturbances($terrafundSiteSubmission);
        $this->tree_species = $this->getTreeSpecies($terrafundSiteSubmission);
        $this->none_tree_species = $this->getNoneTreeSpecies($terrafundSiteSubmission);
        $this->shared_drive_link = $terrafundSiteSubmission->shared_drive_link;
        $this->terrafund_site_id = $terrafundSiteSubmission->terrafund_site_id;
        $this->created_at = $terrafundSiteSubmission->created_at;
        $this->updated_at = $terrafundSiteSubmission->updated_at;
        $this->due_at = data_get($terrafundSiteSubmission->terrafundDueSubmission, 'due_at', null);
    }

    private function getPhotos($submission)
    {
        $resources = [];
        foreach ($submission->terrafundFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }

    private function getTreeSpecies($nursery)
    {
        $resources = [];
        foreach ($nursery->terrafundTreeSpecies as $terrafundTreeSpecies) {
            $resources[] = new TerrafundTreeSpeciesResource($terrafundTreeSpecies);
        }

        return $resources;
    }

    private function getNoneTreeSpecies($submission)
    {
        $resources = [];
        foreach ($submission->terrafundNoneTreeSpecies as $terrafundNoneTreeSpecies) {
            $resources[] = new TerrafundNoneTreeSpeciesResource($terrafundNoneTreeSpecies);
        }

        return $resources;
    }

    private function getDisturbances($submission)
    {
        $resources = [];
        foreach ($submission->disturbances as $disturbance) {
            $resources[] = new TerrafundDisturbanceResource($disturbance);
        }

        return $resources;
    }
}
