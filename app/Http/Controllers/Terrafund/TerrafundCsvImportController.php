<?php

namespace App\Http\Controllers\Terrafund;

use App\Exceptions\CsvContainsEmptyCellsException;
use App\Exceptions\CsvHasIncorrectHeadersException;
use App\Exceptions\Terrafund\InvalidTerrafundFileUploadException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreTerrafundCsvImportRequest;
use App\Jobs\CreateTerrafundTreeSpeciesJob;
use App\Jobs\RemoveTerrafundTreeSpeciesByImportJob;
use App\Models\Terrafund\TerrafundCsvImport;
use App\Resources\Terrafund\TerrafundCsvImportResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;

class TerrafundCsvImportController extends Controller
{
    public const TREE_SPECIES_HEADER = 'Tree Species';
    public const COUNT_HEADER = 'Amount';

    public function createAction(StoreTerrafundCsvImportRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $treeable = getTerrafundModelDataFromMorphable($data['treeable_type'], $data['treeable_id']);

        $this->authorize('createTree', $treeable['model']);

        $data['file'] = UploadHelper::findByIdAndValidate(
            $data['upload_id'],
            UploadHelper::FILES_CSV,
            Auth::user()->id
        );

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
            throw new InvalidTerrafundFileUploadException();
        }

        if ($headers[0] !== self::TREE_SPECIES_HEADER || $headers[1] !== self::COUNT_HEADER) {
            throw new CsvHasIncorrectHeadersException();
        }

        $records = $csv->getRecords();

        $csvImport = new TerrafundCsvImport();
        $csvImport->importable_type = get_class($treeable['model']);
        $csvImport->importable_id = $treeable['model']->id;
        $csvImport->total_rows = count($csv);
        $csvImport->has_failed = false;
        $csvImport->saveOrFail();

        foreach ($records as $record) {
            if ($record[self::TREE_SPECIES_HEADER] === null || $record[self::COUNT_HEADER] === null) {
                $csvImport = TerrafundCsvImport::where('id', $csvImport->id)->firstOrFail();
                $csvImport->has_failed = true;
                $csvImport->saveOrFail();
                RemoveTerrafundTreeSpeciesByImportJob::dispatch($csvImport->id);

                throw new CsvContainsEmptyCellsException();

                break;
            } else {
                CreateTerrafundTreeSpeciesJob::dispatch($record[self::TREE_SPECIES_HEADER], $record[self::COUNT_HEADER], $csvImport->importable_type, $csvImport->importable_id, $csvImport->id);
            }
        }

        $fileService->delete($path);

        return JsonResponseHelper::success((object) new TerrafundCsvImportResource($csvImport), 200);
    }
}
