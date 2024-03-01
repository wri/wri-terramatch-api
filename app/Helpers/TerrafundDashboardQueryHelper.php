<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest($request)
    {
        $query = Project::query();
        $query = $query->where('framework_key', 'terrafund');
        $query = $query->whereHas('organisation', function ($query) {
            $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
        });
        if ($request->has('country')) {
            $country = $request->input('country');
            $query->where('country', $country);
        } elseif ($request->has('uuid')) {
            $projectId = $request->input('uuid');
            $query->where('uuid', $projectId);
        }

        return $query;
    }
}
