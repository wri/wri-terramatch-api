<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest(Request $request)
    {
        $query = QueryBuilder::for(Project::class)
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->select('v2_projects.*',
                'organisations.name as organisation_name',
                'organisations.type as organisation_type',
            )
            ->allowedFilters([
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('landscape'),
                AllowedFilter::exact('country'),
                AllowedFilter::exact('organisations.type'),
                AllowedFilter::exact('v2_projects.status'),
            ]);

        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where(function ($query) use ($searchTerm) {
                $query->where('v2_projects.name', 'like', "%$searchTerm%")
                    ->orWhere('v2_projects.framework_key', 'like', "%$searchTerm%");
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
        ->pluck('uuid')->first();

        return self::retrievePolygonUuidsForProject($projectUuId);
    }

    public static function getPolygonUuidsOfProject($request)
    {
        $projectUuId = $request->input('uuid');

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
            ->pluck('uuid')->first();

        return self::retrievePolygonUuidsByStatusForProject($projectUuid);
    }

    public static function getPolygonsUuidsByStatusForProject($request)
    {
        $projectUuid = $request->input('uuid');

        return self::retrievePolygonUuidsByStatusForProject($projectUuid);
    }
}
