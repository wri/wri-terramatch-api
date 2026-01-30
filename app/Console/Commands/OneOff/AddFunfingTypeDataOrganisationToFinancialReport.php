<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialReport;
use App\Models\V2\FundingType;
use Illuminate\Console\Command;

class AddFunfingTypeDataOrganisationToFinancialReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:add-funfing-type-data-organisation-to-financial-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add funfing type data organisation to financial report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        FinancialReport::all()->each(function (FinancialReport $financialReport) {
            if ($financialReport->fundingTypes()->count() > 0) {
                return;
            }
            $organisationUuid = $financialReport->organisation()->value('uuid');
            $fundingTypes = FundingType::where('organisation_id', $organisationUuid)->whereNull('financial_report_id')->get();
            foreach ($fundingTypes as $fundingType) {
                FundingType::create([
                    'organisation_id' => $organisationUuid,
                    'source' => $fundingType->source,
                    'amount' => $fundingType->amount,
                    'year' => $fundingType->year,
                    'type' => $fundingType->type,
                    'financial_report_id' => $financialReport->id,
                ]);
            }
        });
        $this->info('Funding types added to financial report');
    }
}
