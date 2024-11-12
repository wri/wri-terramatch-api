<?php

namespace App\Services;

use App\Helpers\GeometryHelper;
use Illuminate\Support\Facades\Log;
use App\Models\V2\PolygonGeometry;

class SiteValidationService
{
    public function validateSite(string $siteUuid)
    {
        $sitePolygonsUuids = GeometryHelper::getSitePolygonsUuids($siteUuid);
        $checkedPolygons = [];

        foreach ($sitePolygonsUuids as $polygonUuid) {
            $criteriaData = $this->getCriteriaData($polygonUuid);

            if (isset($criteriaData['error'])) {
                Log::error('Error fetching criteria data', ['polygon_uuid' => $polygonUuid, 'error' => $criteriaData['error']]);
                $checkedPolygons[] = [
                    'uuid' => $polygonUuid,
                    'valid' => false,
                    'checked' => false,
                    'nonValidCriteria' => [],
                ];

                continue;
            }

            $isValid = true;
            $nonValidCriteria = [];
            if (empty($criteriaData['criteria_list'])) {
                $isValid = false;
            } else {
                foreach ($criteriaData['criteria_list'] as $criteria) {
                    if ($criteria['valid'] == 0) {
                        $isValid = false;
                        $nonValidCriteria[] = $criteria;
                    }
                }
            }

            $checkedPolygons[] = [
                'uuid' => $polygonUuid,
                'valid' => $isValid,
                'checked' => !empty($criteriaData['criteria_list']),
                'nonValidCriteria' => $nonValidCriteria,
            ];
        }

        return $checkedPolygons;
    }

    private function getCriteriaData($uuid)
    {
        $geometry = PolygonGeometry::isUuid($uuid)->first();
        if ($geometry === null) {
            return response()->json(['error' => 'Polygon not found for the given UUID'], 404);
        }

        $criteriaList = GeometryHelper::getCriteriaDataForPolygonGeometry($geometry);

        if (empty($criteriaList)) {
            return ['error' => 'Criteria data not found for the given polygon ID'];
        }

        return ['polygon_id' => $uuid, 'criteria_list' => $criteriaList];
    }
}
