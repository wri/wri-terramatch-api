<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundNurserySubmission as TerrafundNurserySubmissionModel;
use App\Resources\Resource;

class TerrafundNurserySubmissionResource extends Resource
{
    public function __construct(TerrafundNurserySubmissionModel $terrafundNurserySubmission)
    {
        $this->id = $terrafundNurserySubmission->id;
        $this->seedlings_young_trees = $terrafundNurserySubmission->seedlings_young_trees;
        $this->interesting_facts = $terrafundNurserySubmission->interesting_facts;
        $this->site_prep = $terrafundNurserySubmission->site_prep;
        $this->terrafund_nursery_id = $terrafundNurserySubmission->terrafund_nursery_id;
        $this->photos = $this->getPhotos($terrafundNurserySubmission);
        $this->shared_drive_link = $terrafundNurserySubmission->shared_drive_link;
        $this->created_at = $terrafundNurserySubmission->created_at;
        $this->updated_at = $terrafundNurserySubmission->updated_at;
        $this->due_at = data_get($terrafundNurserySubmission->terrafundDueSubmission, 'due_at', null);
    }

    private function getPhotos($submission)
    {
        $resources = [];
        foreach ($submission->terrafundFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }
}
