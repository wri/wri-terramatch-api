<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\FinancialReport;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;

class RemoveDuplicationFinancialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:remove-duplication-financial-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplication financial data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Organisation::all()->each(function (Organisation $organisation) {
            // Only consider organisation-level indicators (not attached to a financial report)
            $indicators = FinancialIndicators::where('organisation_id', $organisation->id)
                ->whereNull('financial_report_id')
                ->where('collection', '!=', FinancialIndicators::COLLECTION_NOT_COLLECTION_DOCUMENTS)
                ->get();

            if ($indicators->count() == 0) {
                return;
            }

            // Group by unique key: collection + year
            $groups = $indicators->groupBy(function (FinancialIndicators $indicator) {
                return $indicator->collection . '|' . $indicator->year . '|' . $indicator->organisation_id;
            });

            $groups->each(function ($group) {
                if ($group->count() <= 1) {
                    return; // nothing to deduplicate
                }

                // Keep the most recently updated; delete the rest
                $sorted = $group->sortByDesc('updated_at')->values();
                $toKeep = $sorted->first();
                $toDelete = $sorted->slice(1);

                $toDelete->each(function (FinancialIndicators $duplicate) {
                    $duplicate->delete();
                });
            });
        });

        FinancialReport::all()->each(function (FinancialReport $financialReport) {
            $indicators = FinancialIndicators::where('financial_report_id', $financialReport->id)->get();
            if ($indicators->count() == 0) {
                return;
            }

            foreach ($indicators as $indicator) {
                $indicator->organisation_id = $financialReport->organisation_id;
                $indicator->save();
            }
        });

        $this->info('Duplicate financial indicators removed (kept latest by collection+year)');
    }
}
