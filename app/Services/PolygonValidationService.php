<?php

namespace App\Services;

use App\Constants\PolygonFields;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Polygons\EstimatedArea;
use App\Validators\Extensions\Polygons\FeatureBounds;
use App\Validators\Extensions\Polygons\GeometryType;
use App\Validators\Extensions\Polygons\NotOverlapping;
use App\Validators\Extensions\Polygons\PolygonSize;
use App\Validators\Extensions\Polygons\SelfIntersection;
use App\Validators\Extensions\Polygons\Spikes;
use App\Validators\Extensions\Polygons\WithinCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PolygonValidationService
{
    public function validateOverlapping(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            NotOverlapping::getIntersectionData($uuid),
            PolygonService::OVERLAPPING_CRITERIA_ID
        );
    }

    public function checkSelfIntersection(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::where('uuid', $uuid)->first();

        if (! $geometry) {
            return ['error' => 'Geometry not found', 'status' => 404];
        }

        $isSimple = SelfIntersection::uuidValid($uuid);
        $message = $isSimple ? 'The geometry is valid' : 'The geometry has self-intersections';
        $insertionSuccess = App::make(PolygonService::class)
            ->createCriteriaSite($uuid, PolygonService::SELF_CRITERIA_ID, $isSimple);

        return [
            'selfintersects' => $message,
            'geometry_id' => $geometry->id,
            'insertion_success' => $insertionSuccess,
            'valid' => $isSimple,
            'status' => 200,
        ];
    }

    public function validateCoordinateSystem(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            ['valid' => FeatureBounds::uuidValid($uuid)],
            PolygonService::COORDINATE_SYSTEM_CRITERIA_ID
        );
    }

    public function validatePolygonSize(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if (! $geometry) {
            return ['error' => 'Geometry not found', 'status' => 404];
        }

        $areaSqMeters = PolygonSize::calculateSqMeters($geometry->db_geometry);
        $valid = $areaSqMeters <= PolygonSize::SIZE_LIMIT;
        $insertionSuccess = App::make(PolygonService::class)
            ->createCriteriaSite($uuid, PolygonService::SIZE_CRITERIA_ID, $valid);

        return [
            'area_hectares' => $areaSqMeters / 10000, // Convert to hectares
            'area_sqmeters' => $areaSqMeters,
            'geometry_id' => $geometry->id,
            'insertion_success' => $insertionSuccess,
            'valid' => $valid,
            'status' => 200,
        ];
    }

    public function checkWithinCountry(Request $request)
    {
        $polygonUuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $polygonUuid,
            WithinCountry::getIntersectionData($polygonUuid),
            PolygonService::WITHIN_COUNTRY_CRITERIA_ID
        );
    }

    public function checkBoundarySegments(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if (! $geometry) {
            return ['error' => 'Geometry not found', 'status' => 404];
        }
        $spikes = Spikes::detectSpikes($geometry->geo_json);
        $valid = count($spikes) === 0;
        $insertionSuccess = App::make(PolygonService::class)
            ->createCriteriaSite($uuid, PolygonService::SPIKE_CRITERIA_ID, $valid);

        return [
            'spikes' => $spikes,
            'geometry_id' => $uuid,
            'insertion_success' => $insertionSuccess,
            'valid' => $valid,
            'status' => 200,
        ];
    }

    public function getGeometryType(Request $request)
    {
        $uuid = $request->input('uuid');

        $geometryType = PolygonGeometry::getGeometryType($uuid);
        if ($geometryType) {
            $valid = $geometryType === GeometryType::VALID_TYPE_MULTIPOLYGON || $geometryType === GeometryType::VALID_TYPE_POLYGON;
            $insertionSuccess = App::make(PolygonService::class)
                ->createCriteriaSite($uuid, PolygonService::GEOMETRY_TYPE_CRITERIA_ID, $valid);

            return [
                'uuid' => $uuid,
                'geometry_type' => $geometryType,
                'valid' => $valid,
                'insertion_success' => $insertionSuccess,
                'status' => 200,
            ];
        } else {
            return ['error' => 'Geometry not found for the given UUID', 'status' => 404];
        }
    }

    public function validateEstimatedArea(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            EstimatedArea::getAreaData($uuid),
            PolygonService::ESTIMATED_AREA_CRITERIA_ID
        );
    }

    public function validateDataInDB(Request $request)
    {
        $polygonUuid = $request->input('uuid');
        $fieldsToValidate = PolygonFields::BASIC_FIELDS;

        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if (! $sitePolygon) {
            return ['valid' => false, 'message' => 'No site polygon found with the specified UUID.', 'status' => 404];
        }

        $validationErrors = [];
        $polygonService = App::make(PolygonService::class);
        foreach ($fieldsToValidate as $field) {
            $value = $sitePolygon->$field;
            if ($polygonService->isInvalidField($field, $value)) {
                $validationErrors[] = [
                    'field' => $field,
                    'error' => $value,
                    'exists' => ! is_null($value) && $value !== '',
                ];
            }
        }

        $isValid = empty($validationErrors);
        $responseData = ['valid' => $isValid];
        if (! $isValid) {
            $responseData['message'] = 'Some attributes of the site polygon are invalid.';
        }

        $polygonService->createCriteriaSite($polygonUuid, PolygonService::DATA_CRITERIA_ID, $isValid, $validationErrors);

        return array_merge($responseData, ['status' => 200]);
    }

    protected function handlePolygonValidation($polygonUuid, $response, $criteriaId)
    {
        if (isset($response['error']) && $response['error'] != null) {
            $status = $response['status'];
            unset($response['valid']);
            unset($response['status']);

            return $response + ['status' => $status];
        }
        $extraInfo = $response['extra_info'] ?? null;
        $response['insertion_success'] = App::make(PolygonService::class)
            ->createCriteriaSite($polygonUuid, $criteriaId, $response['valid'], $extraInfo);

        return $response;
    }

    public function runValidationPolygon(string $uuid)
    {
        $request = new Request(['uuid' => $uuid]);

        $this->validateOverlapping($request);
        $this->checkSelfIntersection($request);
        $this->validateCoordinateSystem($request);
        $this->validatePolygonSize($request);
        $this->checkWithinCountry($request);
        $this->checkBoundarySegments($request);
        $this->getGeometryType($request);
        $this->validateEstimatedArea($request);
        $this->validateDataInDB($request);
    }
}
