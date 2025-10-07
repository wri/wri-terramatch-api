<?php

namespace App\Http\Controllers\V2\FinancialIndicators;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Models\V2\FinancialIndicators;
use App\Models\V2\FinancialReport;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;

class UpsertFinancialIndicatorsController extends Controller
{
    public function __invoke(Request $request)
    {
        $orgId = Organisation::isUuid($request->organisation_id)->firstOrFail()->id;
        $financialReportId = FinancialReport::isUuid($request->financial_report_id)->first()?->id;

        $updatedRecords = [];

        foreach ($request->documentation_data as $entry) {
            $year = $entry['year'];

            $where = [
                'organisation_id' => $orgId,
                'year' => $year,
                'collection' => FinancialIndicators::COLLECTION_NOT_COLLECTION_DOCUMENTS,
            ];
            if ($financialReportId) {
                $where['financial_report_id'] = $financialReportId;
            }

            $description = $entry['description'] ?? null;
            $exchangeRate = $entry['exchange_rate'] ?? null;
            $existing = FinancialIndicators::where($where)->first();

            if ($existing) {
                $updatedRecords[] = $existing;
            } else {
                $updatedRecords[] = FinancialIndicators::create(array_merge($where, ['description' => $description, 'exchange_rate' => $exchangeRate]));
            }
        }

        $updatedRecords = array_filter($updatedRecords);

        return response()->json(FinancialIndicatorsResource::collection($updatedRecords));
    }
}
