<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Resources\DraftResource;
use App\Resources\Resource;

class TerrafundDueSubmissionResource extends Resource
{
    public function __construct(TerrafundDueSubmission $terrafundDueSubmission)
    {
        $this->id = $terrafundDueSubmission->id;
        $this->terrafund_due_submissionable_type = $terrafundDueSubmission->terrafund_due_submissionable_type;
        $this->terrafund_due_submissionable_id = $terrafundDueSubmission->terrafund_due_submissionable_id;
        $this->terrafund_due_submissionable = $this->getTerrafundDueSubmissionable($terrafundDueSubmission);
        $this->drafts = $this->getDrafts($terrafundDueSubmission);
        $this->due_at = $terrafundDueSubmission->due_at;
        $this->is_submitted = $terrafundDueSubmission->is_submitted;
        $this->unable_report_reason = $terrafundDueSubmission->unable_report_reason;
        $this->created_at = $terrafundDueSubmission->created_at;
        $this->updated_at = $terrafundDueSubmission->updated_at;
    }

    private function getDrafts($terrafundDueSubmission)
    {
        $draftResources = [];
        foreach ($terrafundDueSubmission->drafts as $draft) {
            $draftResources[] = new DraftResource($draft);
        }

        return $draftResources;
    }

    public function getTerrafundDueSubmissionable(TerrafundDueSubmission $terrafundDueSubmission)
    {
        switch ($terrafundDueSubmission->terrafund_due_submissionable_type) {
            case TerrafundProgramme::class:
                return new TerrafundProgrammeResource($terrafundDueSubmission->terrafund_due_submissionable);
            case TerrafundSite::class:
                return new TerrafundSiteResource($terrafundDueSubmission->terrafund_due_submissionable);
            case TerrafundNursery::class:
                return new TerrafundNurseryResource($terrafundDueSubmission->terrafund_due_submissionable);
        }
    }
}
