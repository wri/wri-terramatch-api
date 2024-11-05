<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest(Request $request)
    {
        $filters = $request->all();
        $query = QueryBuilder::for(Project::class)
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->select('v2_projects.*')
            ->where('v2_projects.status', 'approved');

        if (data_get($filters, 'filter.country')) {
            $query->where('v2_projects.country', data_get($filters, 'filter.country'));
        }
        if (data_get($filters, 'filter.programmes')) {
            $query->whereIn('v2_projects.framework_key', data_get($filters, 'filter.programmes'));
        } else {
            $query->whereIn('v2_projects.framework_key', ['terrafund', 'terrafund-landscapes']);
        }

        if (data_get($filters, 'filter.landscapes')) {
            $query->whereIn('v2_projects.landscape', data_get($filters, 'filter.landscapes'));
        }

        if (data_get($filters, 'filter.organisationType')) {
            $query->whereIn('organisations.type', data_get($filters, 'filter.organisationType'));
        } else {
            $query->whereIn('organisations.type', ['non-profit-organization', 'for-profit-organization']);
        }
        if (data_get($filters, 'filter.projectUuid')) {
            $projectUuids = data_get($filters, 'filter.projectUuid');
            if (is_array($projectUuids)) {
                $query->whereIn('v2_projects.uuid', $projectUuids);
            } else {
                $query->where('v2_projects.uuid', $projectUuids);
            }
        }
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

    public static function retrievePolygonUuidsByStatusForProjects($projectUuids, $requestedStatuses = null)
    {
        $statuses = $requestedStatuses ?? ['needs-more-information', 'submitted', 'approved', 'draft'];
        $polygons = [];

        foreach ($projectUuids as $projectUuid) {
            $project = Project::where('uuid', $projectUuid)->first();
            if ($project) {
                $sitePolygons = $project->sitePolygons;

                foreach ($statuses as $status) {
                    $polygonsOfProject = $sitePolygons
                        ->where('status', $status)
                        ->pluck('poly_id');

                    if (! isset($polygons[$status])) {
                        $polygons[$status] = [];
                    }

                    $polygons[$status] = array_merge($polygons[$status], $polygonsOfProject->toArray());
                }
            } else {
                Log::warning("Project with UUID $projectUuid not found.");
            }
        }

        return $polygons;
    }

    public static function getPolygonsByStatusOfProjects($request)
    {
        $projectUuids = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->pluck('v2_projects.uuid');

        $requestedStatuses = $request->input('statuses');

        return self::retrievePolygonUuidsByStatusForProjects($projectUuids, $requestedStatuses);
    }
}
