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
        $model = Organisation::isUuid($request->organisation_id)->firstOrFail();
        $financialReportId = null;
        if ($request->filled('financial_report_id')) {
            $financialReportModel = FinancialReport::isUuid($request->financial_report_id)->first();
            if ($financialReportModel) {
                $financialReportId = $financialReportModel->id;
                if (! is_null($request->financial_year_start_month) && $request->financial_year_start_month !== '') {
                    $financialReportModel->fin_start_month = $request->financial_year_start_month;
                }
                if (! is_null($request->local_currency) && $request->local_currency !== '') {
                    $financialReportModel->currency = $request->local_currency;
                }
                $financialReportModel->save();
            }
        }

        if (! $financialReportId) {
            if (! is_null($request->financial_year_start_month) && $request->financial_year_start_month !== '') {
                $model->fin_start_month = $request->financial_year_start_month;
            }

            if (! is_null($request->local_currency) && $request->local_currency !== '') {
                $model->currency = $request->local_currency;
            }
        }

        $model->save();
        $orgId = $model->id;
        $updatedRecords = [];
        $dataUuids = $request->uuids;

        foreach ($request->profit_analysis_data as $entry) {
            $year = $entry['year'];
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_REVENUE, 'revenueUuid', $entry['revenue'] ?? 0, $financialReportId);
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_EXPENSES, 'expensesUuid', $entry['expenses'] ?? 0, $financialReportId);
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_PROFIT, 'profitUuid', ($entry['revenue'] ?? 0) - ($entry['expenses'] ?? 0), $financialReportId);
        }

        foreach ($request->non_profit_analysis_data as $entry) {
            $year = $entry['year'];
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_BUDGET, 'budgetUuid', $entry['budget'] ?? 0, $financialReportId);
        }

        foreach ($request->current_radio_data as $entry) {
            $year = $entry['year'];
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_CURRENT_ASSETS, 'currentAssetsUuid', $entry['currentAssets'] ?? 0, $financialReportId);
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_CURRENT_LIABILITIES, 'currentLiabilitiesUuid', $entry['currentLiabilities'] ?? 0, $financialReportId);
            $updatedRecords[] = $this->safeUpdateOrCreate($orgId, $year, FinancialIndicators::COLLECTION_CURRENT_RATIO, 'currentRatioUuid', $entry['currentLiabilities'] > 0
            ? $entry['currentAssets'] / $entry['currentLiabilities']
            : 0, $financialReportId);
        }

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
            $existing = FinancialIndicators::where($where)->first();

            if ($existing) {
                if ($description !== null && $existing->description !== $description) {
                    $existing->description = $description;
                    $existing->save();
                }
                $updatedRecords[] = $existing;
            } else {
                $updatedRecords[] = FinancialIndicators::create(array_merge($where, ['description' => $description]));
            }
        }

        $updatedRecords = array_filter($updatedRecords);

        return response()->json(FinancialIndicatorsResource::collection($updatedRecords));
    }

    public function safeUpdateOrCreate($orgId, $year, $collection, $uuidKey, $amount, $financialReportId = null)
    {
        $where = [
            'organisation_id' => $orgId,
            'year' => $year,
            'collection' => $collection,
        ];

        if ($financialReportId) {
            $where['financial_report_id'] = $financialReportId;
        }

        $existing = FinancialIndicators::where($where)->first();

        if ($existing) {
            if ($amount != 0) {
                $existing->amount = $amount;
                $existing->save();
            }

            return $existing;
        }

        if ($this->isInvalidAmount($amount) === false) {
            return FinancialIndicators::create(array_merge($where, ['amount' => $amount]));
        }

        return null;
    }

    public function isInvalidAmount($value)
    {
        return $value === null || $value == 0;
    }
}
