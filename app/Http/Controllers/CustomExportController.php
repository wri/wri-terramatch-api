<?php

namespace App\Http\Controllers;

use App\Helpers\CustomReportHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreCustomExportRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomExportController extends Controller
{
    public function availableFieldsAction(string $key): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $availableProps = CustomReportHelper::availableProperties($key);

        if (empty($availableProps)) {
            return JsonResponseHelper::error('Report not configured/found', 404);
        }

        return JsonResponseHelper::success($availableProps, 200);
    }

    public function generateReportAction(StoreCustomExportRequest $request): BinaryFileResponse
    {
        $data = $request->json()->all();

        $exportable = getDataFromMorphable($data['exportable_type'], $data['exportable_id']);

        $this->authorize('export', $exportable['model']);

        $reporter = CustomReportHelper::getReport($data['exportable_type']);
        if (empty($reporter)) {
            return JsonResponseHelper::error(['Report not configured/found'], 404);
        }

        $reporter->setup($data, $exportable['model']);
        $reporter->generateCSV($reporter->generate(), 'Custom Report - ' . now()->format('dmy') . '.csv');
        $zipFilename = $reporter->zipAndServe();

        return response()->download($zipFilename)->deleteFileAfterSend(true);
    }
}
