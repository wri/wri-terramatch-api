<?php

namespace App\Resources;

use App\Models\DueSubmission as DueSubmissionModel;

class DueProgrammeSubmissionResource extends Resource
{
    public function __construct(DueSubmissionModel $dueSubmission)
    {
        $this->id = $dueSubmission->id;
        $this->due_submissionable_type = $dueSubmission->due_submissionable_type;
        $this->due_submissionable_id = $dueSubmission->due_submissionable_id;
        $this->drafts = $this->getDrafts($dueSubmission);
        $this->due_at = $dueSubmission->due_at->toISOString();
        $this->due_submissionable = new BaseProgrammeResource($dueSubmission->due_submissionable);
        $this->is_submitted = $dueSubmission->is_submitted;
        $this->created_at = $dueSubmission->created_at;
        $this->updated_at = $dueSubmission->updated_at;
    }

    private function getDrafts($dueSubmission)
    {
        $draftResources = [];
        foreach ($dueSubmission->drafts as $draft) {
            $draftResources[] = new DraftResource($draft);
        }

        return $draftResources;
    }
}
