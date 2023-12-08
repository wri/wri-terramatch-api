<?php

namespace App\Observers\Terrafund;

use App\Mail\TerrafundProgrammeSubmissionReceived;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use Illuminate\Support\Facades\Mail;

class TerrafundProgrammeSubmissionObserver
{
    public function created(TerrafundProgrammeSubmission $terrafundProgrammeSubmission)
    {
        if (! in_array(config('app.env'), ['testing', 'pipelines', 'local'])) {
            Mail::to('terramatch@wri.org')->queue(
                new TerrafundProgrammeSubmissionReceived($terrafundProgrammeSubmission->terrafundProgramme->id, $terrafundProgrammeSubmission->terrafundProgramme->name)
            );
        }
    }
}
