<?php

namespace App\Http\Controllers;

use App\Exceptions\CsvContainsEmptyCellsException;
use App\Exceptions\CsvHasIncorrectHeadersException;
use App\Exceptions\InvalidSiteFileUploadException;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\CreateSiteTreeSpeciesJob;
use App\Jobs\RemoveSiteTreeSpeciesByImportJob;
use App\Models\Site;
use App\Models\SiteCsvImport;
use App\Resources\SiteCsvImportResource;
use App\Resources\SiteTreeSpeciesResource;
use App\Validators\SiteTreeSpeciesCsvValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiteTreeSpeciesCsvController extends Controller
{
    public const TREE_SPECIES_HEADER = 'Tree Species';
    public const COUNT_HEADER = 'Amount';

    public function createAction(Request $request, Site $site = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }
        $this->authorize('update', $site);

        if (isset($data['upload_id'])) {
            SiteTreeSpeciesCsvValidator::validate('CREATE_WITH_UPLOAD_ID', $data);
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

        SiteTreeSpeciesCsvValidator::validate('CREATE', $data);

        $csv = Reader::createFromPath($data['file'], 'r');
        $csv->setHeaderOffset(0);

        try {
            $headers = $csv->getHeader();
        } catch (Exception $exception) {
            throw new InvalidSiteFileUploadException();
        }

        if ($headers[0] !== self::TREE_SPECIES_HEADER) {
            throw new CsvHasIncorrectHeadersException();
        }

        if (isset($data['site_submission_id']) && $headers[1] !== self::COUNT_HEADER) {
            throw new CsvHasIncorrectHeadersException();
        }

        $records = $csv->getRecords();

        $csvImport = new SiteCsvImport();
        $csvImport->site_id = $site->id;
        if (isset($data['site_submission_id'])) {
            $csvImport->site_submission_id = $data['site_submission_id'];
        }
        $csvImport->total_rows = count($csv);
        $csvImport->has_failed = false;
        $csvImport->saveOrFail();

        foreach ($records as $record) {
            if ($record[self::TREE_SPECIES_HEADER] === null || (isset($data['site_submission_id']) && $record[self::COUNT_HEADER] === null)) {
                $csvImport = SiteCsvImport::where('id', $csvImport->id)->firstOrFail();
                $csvImport->has_failed = true;
                $csvImport->saveOrFail();
                RemoveSiteTreeSpeciesByImportJob::dispatch($csvImport->id);

                throw new CsvContainsEmptyCellsException();

                break;
            } else {
                if (isset($data['site_submission_id'])) {
                    CreateSiteTreeSpeciesJob::dispatch($record[self::TREE_SPECIES_HEADER], $site->id, $csvImport->id, $record[self::COUNT_HEADER], $data['site_submission_id']);
                } else {
                    CreateSiteTreeSpeciesJob::dispatch($record[self::TREE_SPECIES_HEADER], $site->id, $csvImport->id);
                }
            }
        }

        $fileService->delete($path);

        return JsonResponseHelper::success((object) new SiteCsvImportResource($csvImport), 200);
    }

    public function readAction(SiteCsvImport $siteCsvImport): JsonResponse
    {
        $this->authorize('read', $siteCsvImport);
        $siteCsvImport->load('siteTreeSpecies');

        return JsonResponseHelper::success((object) new SiteCsvImportResource($siteCsvImport), 200);
    }

    public function readTreeSpeciesAction(SiteCsvImport $siteCsvImport): JsonResponse
    {
        $this->authorize('readAll', $siteCsvImport);
        $resources = [];
        foreach ($siteCsvImport->siteTreeSpecies as $siteTreeSpecies) {
            $resources[] = new SiteTreeSpeciesResource($siteTreeSpecies);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function downloadCsvTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'target_trees_planted.csv';
        $path = base_path('resources/templates/target_trees_planted_2.csv');
        $headers = [
            'Content-Type' => 'text/plain',
        ];

        return response()->download($path, $filename, $headers);
    }
}
