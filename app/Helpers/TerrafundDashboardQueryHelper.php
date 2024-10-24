<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest($request)
    {
        $query = QueryBuilder::for(Project::class)
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->select('v2_projects.*')
            ->where('v2_projects.status', 'approved')
            ->whereIn('organisations.type', ['non-profit-organization', 'for-profit-organization'])
            ->whereIn('v2_projects.framework_key', ['terrafund', 'terrafund-landscapes'])
            ->allowedFilters([
                AllowedFilter::exact('framework_key'),
                AllowedFilter::callback('landscapes', function ($query, $value) {
                    $query->whereIn('landscape', $value);
                }),
                AllowedFilter::exact('country'),
                AllowedFilter::callback('organisations.type', function ($query, $value) {
                    $query->whereIn('organisations.type', $value);
                }),
                AllowedFilter::callback('programmes', function ($query, $value) {
                    $query->whereIn('framework_key', $value);
                }),
                AllowedFilter::exact('v2_projects.status'),
                AllowedFilter::exact('v2_projects.uuid'),
            ]);

        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where(function ($query) use ($searchTerm) {
                $query->where('v2_projects.name', 'like', "%$searchTerm%");
            });
        }

        return $query;
    }

    public static function retrievePolygonUuidsForProject($projectUuId)
    {
        $project = Project::where('uuid', $projectUuId)->first();
        $sitePolygons = $project->sitePolygons;

        $polygonsIds = $sitePolygons->pluck('poly_id');

        return $polygonsIds;
    }

    public static function getPolygonIdsOfProject($request)
    {
        $projectUuId = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
        ->pluck('v2_projects.uuid')->first();

        return self::retrievePolygonUuidsForProject($projectUuId);
    }

    public static function getPolygonUuidsOfProject($request)
    {
        $projectUuId = $request['filter']['v2_projects.uuid'];

        return self::retrievePolygonUuidsForProject($projectUuId);
    }

    public static function getPolygonsByStatus()
    {
        try {
            $statuses = ['needs-more-information', 'submitted', 'approved', 'draft'];
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

    public static function retrievePolygonUuidsByStatusForProject($projectUuid)
    {
        $project = Project::where('uuid', $projectUuid)->first();
        $sitePolygons = $project->sitePolygons;
        $statuses = ['needs-more-information', 'submitted', 'approved','draft'];
        $polygons = [];

        foreach ($statuses as $status) {
            $polygonsOfProject = $sitePolygons
                ->where('status', $status)
                ->pluck('poly_id');

            $polygons[$status] = $polygonsOfProject;
        }

        return $polygons;
    }

    public static function getPolygonsByStatusOfProject($request)
    {
        $projectUuid = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->pluck('v2_projects.uuid')->first();

        return self::retrievePolygonUuidsByStatusForProject($projectUuid);
    }

    public static function getPolygonsUuidsByStatusForProject($request)
    {
        $projectUuid = $request->input('uuid');

        return self::retrievePolygonUuidsByStatusForProject($projectUuid);
    }
}
