<?php

namespace App\Validators\Extensions\Polygons;

use App\Validators\Extensions\Extension;

class DuplicateGeometryValidator extends Extension
{
    public static $name = 'duplicate_geometry';

    public static $message = [
        'key' => 'DUPLICATE_GEOMETRY',
        'message' => 'This geometry already exists in the project',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (!is_array($value) || !isset($value['properties']['site_id'])) {
            return true;
        }

        $siteId = $value['properties']['site_id'];
        
        $allData = $validator->getData();
        if (!isset($allData['features']) || !is_array($allData['features'])) {
            return true;
        }

        $result = DuplicateGeometry::checkNewFeaturesDuplicates($allData['features'], $siteId);
        
        if (preg_match('/features\.(\d+)/', $attribute, $matches)) {
            $currentIndex = (int)$matches[1];
            return !in_array($currentIndex, $result['duplicates']);
        }

        return $result['valid'];
    }
}
