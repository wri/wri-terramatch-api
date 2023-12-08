<?php

namespace App\Observers\Terrafund;

use App\Mail\TerrafundProgrammeSubmissionReceived;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Illuminate\Support\Facades\Mail;

class TerrafundSiteSubmissionObserver
{
    public function created(TerrafundSiteSubmission $terrafundSiteSubmission)
    {
        if (! in_array(config('app.env'), ['testing', 'pipelines', 'local'])) {
            Mail::to('terramatch@wri.org')->queue(
                new TerrafundProgrammeSubmissionReceived($terrafundSiteSubmission->terrafundSite->terrafundProgramme->id, $terrafundSiteSubmission->terrafundSite->terrafundProgramme->name)
            );
        }
    }
}
