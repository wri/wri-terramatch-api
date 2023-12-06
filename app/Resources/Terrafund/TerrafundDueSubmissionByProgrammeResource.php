<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Resources\Resource;

class TerrafundDueSubmissionByProgrammeResource extends Resource
{
    public function __construct(TerrafundProgramme $terrafundProgramme, array $dueSubmissionResources)
    {
        $this->terrafund_programme_id = $terrafundProgramme->id;
        $this->terrafund_programme_name = $terrafundProgramme->name;
        $this->terrafund_programme_has_sites = $this->getHasTerrafundSites($terrafundProgramme);
        $this->terrafund_programme_has_nurseries = $this->getHasTerrafundSNurseries($terrafundProgramme);
        $this->terrafund_last_submission_creation_date = $this->getLastSubmissionCreationDate($terrafundProgramme);
        $this->terrafund_next_submission_due_date = $this->getNextSubmissionDueDate($terrafundProgramme);
        $this->terrafund_due_submissions = $dueSubmissionResources;
    }

    public function getHasTerrafundSites(TerrafundProgramme $terrafundProgramme)
    {
        return $terrafundProgramme->terrafundSites->count() > 0;
    }

    public function getHasTerrafundSNurseries(TerrafundProgramme $terrafundProgramme)
    {
        return $terrafundProgramme->terrafundNurseries->count() > 0;
    }

    private function getLastSubmissionCreationDate(TerrafundProgramme $terrafundProgramme)
    {
        $lastSubmission = TerrafundDueSubmission::submitted()
//          ->where('terrafund_programme_id', $terrafundProgramme->id)
            ->where('terrafund_due_submissionable_type', TerrafundProgramme::class)
            ->where('terrafund_due_submissionable_id', $terrafundProgramme->id)
        ->orderByDesc('submitted_at')
        ->first();

        return  data_get($lastSubmission, 'submitted_at', null);
    }

    private function getNextSubmissionDueDate(TerrafundProgramme $terrafundProgramme)
    {
        $nextDue = TerrafundDueSubmission::unsubmitted()
//          ->where('terrafund_programme_id', $terrafundProgramme->id)
            ->where('terrafund_due_submissionable_type', TerrafundProgramme::class)
            ->where('terrafund_due_submissionable_id', $terrafundProgramme->id)
            ->orderBy('due_at')
            ->first();

        return data_get($nextDue, 'due_at', null);
    }
}
