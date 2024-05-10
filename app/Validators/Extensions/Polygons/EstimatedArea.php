<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;

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

        $sumEstArea = $project->sitePolygons()->sum('est_area');
        $lowerBound = self::LOWER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $upperBound = self::UPPER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $valid = $sumEstArea >= $lowerBound && $sumEstArea <= $upperBound;

        return [
            'valid' => $valid,
            'sum_area_project' => $sumEstArea,
            'total_area_project' => $project->total_hectares_restored_goal,
        ];
    }
}
