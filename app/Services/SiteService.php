<?php

namespace App\Services;

use App\Models\V2\Sites\Site;

class SiteService
{
    public static function setSiteToRestorationInProgress($site_uuid)
    {
        if (! $site_uuid) {
            return;
        }
        $site = Site::where('uuid', $site_uuid)->first();
        if (is_null($site)) {
            return;
        }
        $site->restorationInProgress();
    }
}
