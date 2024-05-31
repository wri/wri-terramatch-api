<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;

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
        $projectUuId = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
        ->pluck('uuid')->first();

        $project = Project::where('uuid', $projectUuId)->first();
        $sitePolygons = $project->sitePolygons;

        $polygonsIds = $sitePolygons->pluck('poly_id');

        return $polygonsIds;
    }

    public static function getPolygonsByStatusOfProject($request)
    {
        try {
            $projectUuId = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
                ->pluck('uuid')->first();
            Log::info('Getting project statuses ' . $request);
            $project = Project::where('uuid', $projectUuId)->first();
            $sitePolygons = $project->sitePolygons;
            $statuses = ['needs-more-info', 'submitted', 'approved'];
            $polygons = [];
            foreach ($statuses as $status) {
                $polygonsOfProject = $sitePolygons
                    ->where('status', $status)
                    ->pluck('poly_id');

                $polygons[$status] = $polygonsOfProject;
            }

            return $polygons;
        } catch (\Exception $e) {
            Log::error('Error fetching polygons by status of project: ' . $e->getMessage());

            return [];
        }
    }

    public static function getPolygonsByStatus()
    {
        try {
            $statuses = ['needs-more-info', 'submitted', 'approved'];
            $polygons = [];
            foreach ($statuses as $status) {
                $polygonsOfProject = SitePolygon::where('status', $status)
                ->whereNotNull('site_id')
                ->where('site_id', '!=', 'NULL')
                ->pluck('poly_id');

                $polygons[$status] = $polygonsOfProject;
            }

            return $polygons;
        } catch (\Exception $e) {
            Log::error('Error fetching polygons by status of project: ' . $e->getMessage());

            return [];
        }
    }
}
