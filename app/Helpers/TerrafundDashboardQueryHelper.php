<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest($request)
    {
        $projects = Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
                $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
            });
        if ($request->has('country')) {
            $country = $request->input('country');
            $projects->where('country', $country);
        } elseif ($request->has('uuid')) {
            $projectId = $request->input('uuid');
            $projects->where('uuid', $projectId);
        }

        return $projects;
    }
}
