<?php

namespace App\Http\Controllers;

use App\Exceptions\CsvContainsEmptyCellsException;
use App\Exceptions\CsvHasIncorrectHeadersException;
use App\Exceptions\InvalidProgrammeFileUploadException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreProgrammeTreeSpeciesCsvRequest;
use App\Jobs\CreateProgrammeTreeSpeciesJob;
use App\Jobs\RemoveProgrammeTreeSpeciesByImportJob;
use App\Models\CsvImport;
use App\Models\Programme;
use App\Resources\CsvImportResource;
use App\Resources\ProgrammeTreeSpeciesResource;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProgrammeTreeSpeciesCsvController extends Controller
{
    public const TREE_SPECIES_HEADER = 'Tree Species';
    public const COUNT_HEADER = 'Amount';

    public function createAction(StoreProgrammeTreeSpeciesCsvRequest $request, Programme $programme = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($programme)) {
            if (! isset($data['programme_id'])) {
                throw new ModelNotFoundException();
            }
            $programme = Programme::where('id', $data['programme_id'])->firstOrFail();
            unset($data['programme_id']);
        }
        $this->authorize('createTreeSpecies', $programme);

        if (isset($data['upload_id'])) {
            $data['file'] = UploadHelper::findByIdAndValidate(
                $data['upload_id'],
                UploadHelper::FILES,
                Auth::user()->id
            );
            unset($data['upload_id']);
        }

        $fileService = App::make(\App\Services\FileService::class);
        if (isset($data['file'])) {
            $path = $data['file']['location'];
            $data['file'] = $fileService->clone($path);
        }

        $csv = Reader::createFromPath($data['file'], 'r');
        $csv->setHeaderOffset(0);

        try {
            $headers = $csv->getHeader();
        } catch (Exception $exception) {
            throw new InvalidProgrammeFileUploadException();
        }

        if ($headers[0] !== self::TREE_SPECIES_HEADER) {
            throw new CsvHasIncorrectHeadersException();
        }

        if (isset($data['programme_submission_id']) && $headers[1] !== self::COUNT_HEADER) {
            throw new CsvHasIncorrectHeadersException();
        }

        $records = $csv->getRecords();

        $csvImport = new CsvImport();
        $csvImport->programme_id = $programme->id;
        if (isset($data['programme_submission_id'])) {
            $csvImport->programme_submission_id = $data['programme_submission_id'];
        }
        $csvImport->total_rows = count($csv);
        $csvImport->saveOrFail();

        foreach ($records as $record) {
            if ($record[self::TREE_SPECIES_HEADER] === null || (isset($data['programme_submission_id']) && $record[self::COUNT_HEADER] === null)) {
                $csvImport->status = 'failed';
                $csvImport->saveOrFail();
                RemoveProgrammeTreeSpeciesByImportJob::dispatch($csvImport->id);

                throw new CsvContainsEmptyCellsException();

                break;
            } else {
                if (isset($data['programme_submission_id'])) {
                    CreateProgrammeTreeSpeciesJob::dispatch($record[self::TREE_SPECIES_HEADER], $programme->id, $csvImport->id, $record[self::COUNT_HEADER], $data['programme_submission_id']);
                } else {
                    CreateProgrammeTreeSpeciesJob::dispatch($record[self::TREE_SPECIES_HEADER], $programme->id, $csvImport->id);
                }
            }
        }

        $fileService->delete($path);

        return JsonResponseHelper::success((object) new CsvImportResource($csvImport), 200);
    }

    public function readAction(CsvImport $csvImport): JsonResponse
    {
        $this->authorize('read', $csvImport);
        $csvImport->load('programmeTreeSpecies');

        return JsonResponseHelper::success((object) new CsvImportResource($csvImport), 200);
    }

    public function readTreeSpeciesAction(CsvImport $csvImport): JsonResponse
    {
        $this->authorize('readAll', $csvImport);
        $resources = [];
        foreach ($csvImport->programmeTreeSpecies as $programmeTreeSpecies) {
            $resources[] = new ProgrammeTreeSpeciesResource($programmeTreeSpecies);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function downloadCsvTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'target_trees_planted.csv';
        $path = base_path('resources/templates/target_trees_planted.csv');
        $headers = [
            'Content-Type' => 'text/plain',
        ];

        return response()->download($path, $filename, $headers);
    }

    public function downloadCsvExample(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'report_trees_planted.csv';
        $path = base_path('resources/templates/report_trees_planted_2.csv');
        $headers = [
            'Content-Type' => 'text/plain',
        ];

        return response()->download($path, $filename, $headers);
    }
}
