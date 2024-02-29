<?php

namespace App\Helpers;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest($query, $request)
    {
        $query = $query->where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
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
