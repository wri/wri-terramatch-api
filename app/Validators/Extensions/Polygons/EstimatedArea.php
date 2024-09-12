<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\Log;

class EstimatedArea extends Extension
{
    public static $name = 'estimated_area';

    public static $message = [
        'key' => 'TOTAL_AREA_EXPECTED',
        'message' => 'The project\'s total geometry must match the project\'s estimated area.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return self::getAreaData($value)['valid'];
    }

    public const LOWER_BOUND_MULTIPLIER = 0.75;
    public const UPPER_BOUND_MULTIPLIER = 1.25;

    public static function getAreaData(string $polygonUuid): array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if ($sitePolygon == null) {
            return ['valid' => false, 'error' => 'Site polygon not found for the given polygon ID', 'status' => 404];
        }

        $project = $sitePolygon->project;
        if ($project == null) {
            return [
                'valid' => false,
                'error' => 'Project not found for the given SitePolygon',
                'sitePolygonId' => $sitePolygon->uuid,
                'status' => 404,
            ];
        }

        if (empty($project->total_hectares_restored_goal)) {
            return ['valid' => false, 'error' => 'Total hectares restored goal not set for the project', 'status' => 500];
        }

        $sumEstArea = $project->sitePolygons()->sum('calc_area');
        $lowerBound = self::LOWER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $upperBound = self::UPPER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $valid = $sumEstArea >= $lowerBound && $sumEstArea <= $upperBound;
        $percentage = ($sumEstArea / $project->total_hectares_restored_goal) * 100;
        $sumEstArea = round($sumEstArea);
        $percentage = round($percentage);
        $extra_info = [
          'sum_area' => $sumEstArea,
          'percentage' => $percentage,
          'total_area_project' => $project->total_hectares_restored_goal,
        ];

        return [
          'valid' => $valid,
          'sum_area_project' => $sumEstArea,
          'total_area_project' => $project->total_hectares_restored_goal,
          'extra_info' => $extra_info,
        ];
    }
    public static function getAreaDataSite(string $polygonUuid): array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if ($sitePolygon == null) {
            return ['valid' => false, 'error' => 'Site polygon not found for the given polygon ID', 'status' => 404];
        }

        $site = $sitePolygon->site;
        $sumEstArea = $site->sitePolygons()->sum('calc_area');
        $lowerBound = self::LOWER_BOUND_MULTIPLIER * $site->hectares_to_restore_goal;
        $upperBound = self::UPPER_BOUND_MULTIPLIER * $site->hectares_to_restore_goal;
        $valid = $sumEstArea >= $lowerBound && $sumEstArea <= $upperBound;
        $percentage = ($sumEstArea / $site->hectares_to_restore_goal) * 100;
        $sumEstArea = round($sumEstArea);
        $percentage = round($percentage);
        $extra_info = [
          'sum_area' => $sumEstArea,
          'percentage' => $percentage,
          'total_area_site' => $site->hectares_to_restore_goal,
        ];
        return [
          'valid' => $valid,
          'sum_area_project' => $sumEstArea,
          'total_area_project' => $site->hectares_to_restore_goal,
          'extra_info' => $extra_info
        ];
    }
    public static function getAreaDataWithSiteID(string $siteUuid): array
    {
        $sitePolygon = SitePolygon::where('site_id', $siteUuid)->first();
        if ($sitePolygon == null) {
            return ['valid' => false, 'error' => 'Site polygon not found for the given polygon ID', 'status' => 404];
        }

        $project = $sitePolygon->project;
        if ($project == null) {
            return [
                'valid' => false,
                'error' => 'Project not found for the given SitePolygon',
                'sitePolygonId' => $sitePolygon->uuid,
                'status' => 404,
            ];
        }

        if (empty($project->total_hectares_restored_goal)) {
            return ['valid' => false, 'error' => 'Total hectares restored goal not set for the project', 'status' => 500];
        }

        $sumEstArea = $project->sitePolygons()->sum('calc_area');
        $lowerBound = self::LOWER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $upperBound = self::UPPER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $valid = $sumEstArea >= $lowerBound && $sumEstArea <= $upperBound;

        return [
            'valid' => $valid,
            'sum_area_project' => $sumEstArea,
            'total_area_project' => $project->total_hectares_restored_goal,
        ];
    }

    public static function getAreaOfProject(string $projectUuid): array
    {
        $project = Project::where('uuid', $projectUuid)->first();
        $sumEstArea = $project->sitePolygons()->sum('calc_area');
        $lowerBound = self::LOWER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $upperBound = self::UPPER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;

        return [
          'sum_area_project' => $sumEstArea,
          'lower_bound' => $lowerBound,
          'upper_bound' => $upperBound,
        ];
    }
}
