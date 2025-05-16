<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlantStartDate extends Extension
{
    public static $name = 'plant_start_date';

    public static $message = [
        'key' => 'PLANT_START_DATE',
        'message' => 'The plant start date must meet all validation criteria.',
    ];

    public const MIN_DATE = '2018-01-01';

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return self::getValidationData($value)['valid'];
    }

    public static function getValidationData(string $polygonUuid): array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if (! $sitePolygon) {
            return [
                'valid' => false,
                'error' => 'Site polygon not found',
                'status' => 404,
                'extra_info' => [
                    'error_type' => 'NOT_FOUND',
                    'polygon_uuid' => $polygonUuid,
                ],
            ];
        }

        $plantStart = $sitePolygon->plantstart;

        if (empty($plantStart)) {
            return [
                'valid' => false,
                'key' => 'MISSING_PLANT_START_DATE',
                'message' => 'Plant start date cannot be empty.',
                'extra_info' => [
                    'error_type' => 'MISSING_VALUE',
                    'polygon_uuid' => $polygonUuid,
                    'polygon_name' => $sitePolygon->poly_name,
                    'site_name' => $sitePolygon->site->name ?? null,
                ],
            ];
        }

        if ($plantStart === '0000-00-00') {
            return [
                'valid' => false,
                'key' => 'INVALID_PLANT_START_DATE_FORMAT',
                'message' => 'Plant start date cannot be \'0000-00-00\'.',
                'extra_info' => [
                    'error_type' => 'INVALID_FORMAT',
                    'polygon_uuid' => $polygonUuid,
                    'polygon_name' => $sitePolygon->poly_name,
                    'site_name' => $sitePolygon->site->name ?? null,
                    'provided_value' => $plantStart,
                ],
            ];
        }

        try {
            $plantStartDate = Carbon::parse($plantStart);
            $minDate = Carbon::parse(self::MIN_DATE);
            $currentDate = Carbon::now();

            if ($plantStartDate->lt($minDate)) {
                return [
                    'valid' => false,
                    'key' => 'PLANT_START_DATE_TOO_EARLY',
                    'message' => 'Plant start date must be on or after January 1, 2018.',
                    'extra_info' => [
                        'error_type' => 'DATE_TOO_EARLY',
                        'polygon_uuid' => $polygonUuid,
                        'polygon_name' => $sitePolygon->poly_name,
                        'site_name' => $sitePolygon->site->name ?? null,
                        'provided_value' => $plantStart,
                        'min_date' => self::MIN_DATE,
                    ],
                ];
            }

            if ($plantStartDate->gt($currentDate)) {
                return [
                    'valid' => false,
                    'key' => 'PLANT_START_DATE_FUTURE',
                    'message' => 'Plant start date must be on or before the current date.',
                    'extra_info' => [
                        'error_type' => 'DATE_IN_FUTURE',
                        'polygon_uuid' => $polygonUuid,
                        'polygon_name' => $sitePolygon->poly_name,
                        'site_name' => $sitePolygon->site->name ?? null,
                        'provided_value' => $plantStart,
                        'current_date' => $currentDate->format('Y-m-d'),
                    ],
                ];
            }

            $site = Site::isUuid($sitePolygon->site_id)->first();
            if ($site && ! empty($site->start_date)) {
                $siteStartDate = Carbon::parse($site->start_date);
                $oneYearBefore = $siteStartDate->copy()->subYear();
                $oneYearAfter = $siteStartDate->copy()->addYear();

                if ($plantStartDate->lt($oneYearBefore) || $plantStartDate->gt($oneYearAfter)) {
                    return [
                        'valid' => false,
                        'key' => 'PLANT_START_DATE_OUTSIDE_RANGE',
                        'message' => 'Plant start date must be within one year of the site\'s establishment date.',
                        'extra_info' => [
                            'error_type' => 'DATE_OUTSIDE_SITE_RANGE',
                            'polygon_uuid' => $polygonUuid,
                            'polygon_name' => $sitePolygon->poly_name,
                            'site_name' => $site->name ?? null,
                            'provided_value' => $plantStart,
                            'site_start_date' => $site->start_date,
                            'allowed_range' => [
                                'min' => $oneYearBefore->format('Y-m-d'),
                                'max' => $oneYearAfter->format('Y-m-d'),
                            ],
                        ],
                    ];
                }
            }

            return [
                'valid' => true,
                'plant_start_date' => $plantStartDate->format('Y-m-d'),
            ];

        } catch (\Exception $e) {
            Log::error('Error validating plant start date', [
                'polygon_uuid' => $polygonUuid,
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'key' => 'INVALID_PLANT_START_DATE',
                'message' => 'Invalid plant start date format.',
                'error' => $e->getMessage(),
                'status' => 500,
                'extra_info' => [
                    'error_type' => 'PARSE_ERROR',
                    'polygon_uuid' => $polygonUuid,
                    'polygon_name' => $sitePolygon->poly_name ?? null,
                    'site_name' => $sitePolygon->site->name ?? null,
                    'provided_value' => $plantStart ?? null,
                    'error_details' => $e->getMessage(),
                ],
            ];
        }
    }

    public static function uuidValid($uuid): bool
    {
        return self::getValidationData($uuid)['valid'];
    }
}
