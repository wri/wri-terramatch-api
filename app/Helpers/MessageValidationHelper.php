<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class MessageValidationHelper
{

    private const DATA_SCHEMA_ATTRIBUTES = [
        'poly_name' => 'Polygon Name',
        'plantstart' => 'Plant Start',
        'plantend' => 'Plant End',
        'practice' => 'Restoration Practice',
        'target_sys' => 'Target Land Use System',
        'distr' => 'Tree Distribution',
        'num_trees' => 'Number of Trees'
    ];
    public function formatCriteriaList($criteriaList)
    {
        foreach ($criteriaList as $criteria) {
            if ($criteria->extra_info) {
                $criteria->messages = $this->generateMessages($criteria->criteria_id, json_decode($criteria->extra_info, true));
                unset($criteria['extra_info']);
            } else {
                $criteria->messages = [];
            }
        }
        return $criteriaList;
    }

    private function generateMessages($criteriaId, $extraInfo)
    {
        $messages = [];
        switch ($criteriaId) {
            case 3:
                foreach ($extraInfo as $info) {
                    if ($info['intersectSmaller']) {
                        $messages[] = "Geometries intersect: approx. {$info['percentage']}% of another, smaller polygon (" . ($info['poly_name'] ?? 'Unnamed polygon') . ")";
                    } else {
                        $messages[] = "Geometries intersect: approx. {$info['percentage']}% of this polygon is intersected by " . ($info['poly_name'] ?? 'Unnamed polygon');
                    }
                }
                break;
            case 12:
                $messages[] = "Project Goal: Sum of all project polygons {$extraInfo['sum_area']} is {$extraInfo['percentage']}% of total hectares to be restored {$extraInfo['total_area_project']}";
                break;
            case 14:
                foreach ($extraInfo as $info) {
                    Log::info('Extra info', ['info' => $info]);
                    if (!$info['exists']) {
                        $messages[] = self::DATA_SCHEMA_ATTRIBUTES[$info['field']] . " is missing.";
                    } else {
                        if ($info['field'] === 'practice') {
                            $messages[] = self::DATA_SCHEMA_ATTRIBUTES[$info['field']] . ": " . $info['error'] . "is not a valid practice because it is not one of [“tree-planting”, “direct-seeding“, “assisted-natural-regeneration”].";
                        } else if ($info['field'] === 'target_sys') {
                            $messages[] = self::DATA_SCHEMA_ATTRIBUTES[$info['field']] . ": " . $info['error'] . " is not a valid Target System because it is not one of [“agroforest”, “natural-forest“, “mangrove”, “peatland”, “riparian-area-or-wetland”, “silvopasture”, “woodlot-or-plantation”, “urban-forest”].";
                        } else if ($info['field'] === 'distr') {
                            $messages[] = self::DATA_SCHEMA_ATTRIBUTES[$info['field']] . ": " . $info['error'] . " is not a valid Tree Distribution because it is not one of [“single-line”, “partial“, “full”].";
                        } else {
                            $messages[] = self::DATA_SCHEMA_ATTRIBUTES[$info['field']] . ": " . $info['error'] . " is invalid.";
                        }

                    }
                }
                break;
            // Add more cases as needed for other criteria_ids
            default:
                $messages[] = "No specific message format for criteria ID: {$criteriaId}";
                break;
        }
        return $messages;
    }
}