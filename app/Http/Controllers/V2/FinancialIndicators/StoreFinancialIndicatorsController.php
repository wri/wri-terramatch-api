<?php

namespace App\Http\Controllers\V2\FinancialIndicators;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FinancialIndicatorsResource;
use App\Models\V2\FinancialIndicators;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;

class StoreFinancialIndicatorsController extends Controller
{
    public function __invoke(Request $request)
    {
        $model = Organisation::isUuid($request->organisation_id)->firstOrFail();
        $this->authorize('read', $model);

        if (! is_null($request->financial_year_start_month) && $request->financial_year_start_month !== '') {
            $model->fin_start_month = $request->financial_year_start_month;
        }

        if (! is_null($request->local_currency) && $request->local_currency !== '') {
            $model->currency = $request->local_currency;
        }

        $model->save();
        $orgId = $model->id;

        if (str_contains($model->type, 'for-profit')) {
            foreach ($request->profit_analysis_data as $entry) {
                $year = $entry['year'];

                $records[] = FinancialIndicators::create([
                    'organisation_id' => $orgId,
                    'year' => $year,
                    'collection' => FinancialIndicators::COLLECTION_REVENUE,
                    'amount' => $entry['revenue'] ?? 0,
                ]);

                $records[] = FinancialIndicators::create([
                    'organisation_id' => $orgId,
                    'year' => $year,
                    'collection' => FinancialIndicators::COLLECTION_EXPENSES,
                    'amount' => $entry['expenses'] ?? 0,
                ]);

                $records[] = FinancialIndicators::create([
                    'organisation_id' => $orgId,
                    'year' => $year,
                    'collection' => FinancialIndicators::COLLECTION_PROFIT,
                    'amount' => ($entry['revenue'] ?? 0) - ($entry['expenses'] ?? 0),
                ]);
            }
        }

        if (str_contains($model->type, 'non-profit')) {
            foreach ($request->profit_analysis_data as $entry) {
                $year = $entry['year'];

                $records[] = FinancialIndicators::create([
                    'organisation_id' => $orgId,
                    'year' => $year,
                    'collection' => FinancialIndicators::COLLECTION_BUDGET,
                    'amount' => $entry['budget'] ?? 0,
                ]);
            }
        }

        foreach ($request->current_radio_data as $entry) {
            $year = $entry['year'];

            $records[] = FinancialIndicators::create([
                'organisation_id' => $orgId,
                'year' => $year,
                'collection' => FinancialIndicators::COLLECTION_CURRENT_ASSETS,
                'amount' => $entry['currentAssets'] ?? 0,
            ]);

            $records[] = FinancialIndicators::create([
                'organisation_id' => $orgId,
                'year' => $year,
                'collection' => FinancialIndicators::COLLECTION_CURRENT_LIABILITIES,
                'amount' => $entry['currentLiabilities'] ?? 0,
            ]);

            $records[] = FinancialIndicators::create([
                'organisation_id' => $orgId,
                'year' => $year,
                'collection' => FinancialIndicators::COLLECTION_CURRENT_RATIO,
                'amount' => $entry['currentLiabilities'] > 0 ? $entry['currentAssets'] / $entry['currentLiabilities'] : null,
            ]);
        }

        foreach ($request->documentation_data as $entry) {
            $year = $entry['year'];

            $records[] = FinancialIndicators::create([
                'organisation_id' => $orgId,
                'year' => $year,
                'description' => $entry['description'] ?? null,
            ]);
        }

        return response()->json(FinancialIndicatorsResource::collection($records));
    }
}
