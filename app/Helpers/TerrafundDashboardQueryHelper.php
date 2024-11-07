<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TerrafundDashboardQueryHelper
{
    public static function buildQueryFromRequest(Request $request)
    {
        $filters = $request->all();
        $searchTerm = $request->query('search');

        $query = Project::query()
            ->select([
                'v2_projects.id',
                'v2_projects.uuid',
                'v2_projects.framework_key',
                'v2_projects.organisation_id',
                'v2_projects.status',
                'v2_projects.name',
                'v2_projects.country',
            ])
            ->with('organisation:id,type,name')
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->where('v2_projects.status', 'approved');

        $query->when(data_get($filters, 'filter.country'), function ($query, $country) {
            $query->where('v2_projects.country', $country);
        });

        $query->when(data_get($filters, 'filter.programmes'), function ($query, $programmes) {
            $query->whereIn('v2_projects.framework_key', $programmes);
        }, function ($query) {
            $query->whereIn('v2_projects.framework_key', ['terrafund', 'terrafund-landscapes']);
        });

        $query->when(data_get($filters, 'filter.landscapes'), function ($query, $landscapes) {
            $query->whereIn('v2_projects.landscape', $landscapes);
        });

        $query->when(data_get($filters, 'filter.organisationType'), function ($query, $organisationType) {
            $query->whereIn('organisations.type', $organisationType);
        }, function ($query) {
            $query->whereIn('organisations.type', ['non-profit-organization', 'for-profit-organization']);
        });

        $query->when(data_get($filters, 'filter.projectUuid'), function ($query, $projectUuid) {
            if (is_array($projectUuid)) {
                $query->whereIn('v2_projects.uuid', $projectUuid);
            } else {
                $query->where('v2_projects.uuid', $projectUuid);
            }
        });

        $query->when($searchTerm, function ($query, $searchTerm) {
            $query->where('v2_projects.name', 'like', "%$searchTerm%");
        });
        Log::info('SQL Query: ' . $query->toSql());

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
        $projectUuId = $request['filter']['projectUuid'];

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

        $approvedStatus = ['approved'];

        return self::retrievePolygonUuidsByStatusForProjects($projectUuids, $approvedStatus);
    }
}
