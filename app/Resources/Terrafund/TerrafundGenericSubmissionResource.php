<?php

namespace App\Resources\Terrafund;

use App\Resources\Resource;

class TerrafundGenericSubmissionResource extends Resource
{
    public function __construct($terrafundSubmission)
    {
        $submissionable = $terrafundSubmission->terrafundDueSubmissionable();

        $this->submission_id = $terrafundSubmission->id;
        $this->submission_type = $this->getType($terrafundSubmission);

        $this->due_submissionable_id = data_get($submissionable, 'id', null);
        $this->due_submissionable_type = $this->getType($submissionable);

        $this->due_submissionable_name = $submissionable->name;

        $this->due_at = data_get($terrafundSubmission->terrafundDueSubmission, 'due_at', null);
        $this->created_at = $terrafundSubmission->created_at;
    }

    private function getType($terrafundSubmission)
    {
        return get_class($terrafundSubmission);
        //        $reflect = new \ReflectionClass($terrafundSubmission);
        //        return $reflect->getShortName();
    }
}
