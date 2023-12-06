<?php

namespace App\Observers\Terrafund;

use App\Mail\TerrafundProgrammeSubmissionReceived;
use App\Models\Terrafund\TerrafundNurserySubmission;
use Illuminate\Support\Facades\Mail;

class TerrafundNurserySubmissionObserver
{
    public function created(TerrafundNurserySubmission $terrafundNurserySubmission)
    {
        if (! in_array(config('app.env'), ['testing', 'pipelines', 'local'])) {
            Mail::to('terramatch@wri.org')->queue(
                new TerrafundProgrammeSubmissionReceived($terrafundNurserySubmission->terrafundNursery->terrafundProgramme->id, $terrafundNurserySubmission->terrafundNursery->terrafundProgramme->name)
            );
        }
    }
}
