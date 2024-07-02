<?php

namespace App\Services;

use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use App\StateMachines\SiteStatusStateMachine;

class SiteService
{
    public static function updateSiteStatus($site_uuid)
    {
        if (! $site_uuid) {
            return;
        }
        $site = Site::where('uuid', $site_uuid)->first();
        if ($site->status != EntityStatusStateMachine::APPROVED) {
            return;
        }
        $site->status = SiteStatusStateMachine::RESTORATION_IN_PROGRESS;
        $site->save();
    }
}
