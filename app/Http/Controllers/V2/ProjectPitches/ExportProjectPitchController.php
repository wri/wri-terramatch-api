<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ProjectPitches\ExportProjectPitchRequest;
use App\Models\V2\ProjectPitch;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportProjectPitchController extends Controller
{
    public function __invoke(ExportProjectPitchRequest $exportProjectPitchRequest): StreamedResponse
    {
        $header = [
            'ID', 'UUID', 'Organisation ID', 'Organisation Name', 'Capacity Building Needs',
            'Total Trees', 'Total Hectares', 'Restoration Intervention Types',
            'Project Country/District', 'Project Country', 'Project Objectives',
            'Project Name', 'Deleted At', 'Created At', 'Updated At',
        ];
        $records = [];

        /**
         * In 2.0 the scope was changed to export all records. I've just commented
         * out the additional functionality, because we will have to put it back.
         */
        // $query = ProjectPitch::whereIn('uuid', $exportProjectPitchRequest->uuids);
        $query = ProjectPitch::query();

        $query->chunkById(100, function ($pitches) use (&$records) {
            $pitches->each(function (ProjectPitch $pitch) use (&$records) {
                if (empty($pitch->organisation)) {
                    return;
                }

                $records[] = [
                    $pitch->id,
                    $pitch->uuid,
                    $pitch->organisation_id,
                    $pitch->organisation->name ?? null,
                    is_array($pitch->capacity_building_needs) ? implode('|', $pitch->capacity_building_needs) : '',
                    $pitch->total_trees,
                    $pitch->total_hectares,
                    is_array($pitch->restoration_intervention_types) ? implode('|', $pitch->restoration_intervention_types) : '',
                    $pitch->project_county_district,
                    $pitch->project_country,
                    $pitch->project_objectives,
                    $pitch->project_name,
                    $pitch->deleted_at,
                    $pitch->created_at,
                    $pitch->updated_at,
                ];
            });
        });

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Project Pitch Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
