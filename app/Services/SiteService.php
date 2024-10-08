<?php

namespace App\Services;

use App\Models\V2\Sites\Site;
use Illuminate\Http\Response;

class SiteService
{
    public static function setSiteToRestorationInProgress($site_uuid)
    {
      try {
        if (! $site_uuid) {
          return;
        }
        $site = Site::where('uuid', $site_uuid)->first();
        if (is_null($site)) {
            return;
        }
        $site->restorationInProgress();
      } catch(\Exception $e) {
        throw new \Exception($e->getMessage(), Response::HTTP_NOT_MODIFIED);
      }
    }
}
