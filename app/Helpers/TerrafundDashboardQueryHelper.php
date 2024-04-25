<?php

namespace App\Helpers;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest($request)
    {
        $query = Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
                $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
            });
        if ($request->has('country')) {
            $country = $request->input('country');
            $query = $query->where('country', $country);
        } elseif ($request->has('uuid')) {
            $projectId = $request->input('uuid');
            $query = $query->where('v2_projects.uuid', $projectId);
        }

        return $query;
    }

    public static function getPolygonIdsOfProject($request)
    {
      $projectIds = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
      ->pluck('id');
      $sitesIds = Site::whereIn('project_id', $projectIds)->pluck('uuid');
      $polygonsIds = SitePolygon::whereIn('site_id', $sitesIds)->pluck('poly_id');
      return $polygonsIds;
    }
}
