<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateOrganisationToFinancialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-organisation-to-financial-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate organisation to financial data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $haritBharatEnterprisesProgrammeUuid = '86b3ea32-8541-4525-b342-2d8010b3cdf7';
        $haritBharatNonProfitProgrammeUuid = 'a8a453a8-658c-48f3-ab79-cf23217bc8ed';
        $landscapeAcceleratorGhanaProgrammeUuid = '7e22bae5-bc8d-44ef-a29f-0d2a2317df21';
        $landscapeAcceleratorAfricaProgrammeUuid = '3d916777-afb1-461b-8e73-2c7efc43a06e';
        $terrafundAFR10EnterprisesProgrammeUuid = 'e80f1187-6ece-4803-a145-7b48c514cc00';
        $terrafundAFR10NonProgrammeUuid = '18f1af1f-8ff3-494b-98e6-1d1c0d44d5d9';


        $revenueLabel = 'revenue';
        $budgetLabel = 'budget';

        $params = [
            [
                'programme_uuid' => $haritBharatEnterprisesProgrammeUuid,
                'organisation_types' => [
                    'for-profit-organization' => [
                        'label' => $revenueLabel,
                        'organisation_revenue_this_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                    'non-profit-organization' => [
                        'label' => $budgetLabel,
                        'organisation_revenue_this_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                ],
            ],
            [
                'programme_uuid' => $haritBharatNonProfitProgrammeUuid,
                'organisation_types' => [
                    'government-agency' => [
                        'label' => $budgetLabel,
                        'fin_budget_current_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                    'non-profit-organization' => [
                        'label' => $budgetLabel,
                        'fin_budget_current_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                ],
            ],
            [
                'programme_uuid' => $landscapeAcceleratorGhanaProgrammeUuid,
                'organisation_types' => [
                    'for-profit-organization' => [
                        'label' => $revenueLabel,
                        'fin_budget_current_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                    'non-profit-organization' => [
                        'label' => $budgetLabel,
                        'fin_budget_current_year' => 2024,
                        'fin_budget_1year' => 2023,
                        'fin_budget_2year' => 2022,
                        'fin_budget_3year' => 2021,
                    ],
                ],
            ],
            [
                'programme_uuid' => $landscapeAcceleratorAfricaProgrammeUuid,
                'organisation_types' => [
                    'for-profit-organization' => [
                        'label' => $revenueLabel,
                        'fin_budget_current_year' => 2024,
                        'fin_budget_1year' => 2023,
                        'fin_budget_2year' => 2022,
                        'fin_budget_3year' => 2021,
                    ],
                    'non-profit-organization' => [
                        'label' => $budgetLabel,
                        'fin_budget_current_year' => 2024,
                        'fin_budget_1year' => 2023,
                        'fin_budget_2year' => 2022,
                        'fin_budget_3year' => 2021,
                    ],
                ],
            ],
            [
                'programme_uuid' => $terrafundAFR10EnterprisesProgrammeUuid,
                'organisation_types' => [
                    'for-profit-organization' => [
                        'label' => $revenueLabel,
                        'fin_budget_current_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                ],
            ],
            [
                'programme_uuid' => $terrafundAFR10NonProgrammeUuid,
                'organisation_types' => [
                    'non-profit-organization' => [
                        'label' => $budgetLabel,
                        'fin_budget_current_year' => 2023,
                        'fin_budget_1year' => 2022,
                        'fin_budget_2year' => 2021,
                        'fin_budget_3year' => 2020,
                    ],
                ],
            ],
        ];

        foreach ($params as $param) {
            Log::info("Processing programme {$param['programme_uuid']}");
            $this->processProgrammeByUuid($param);
        }

    }

    private function processProgrammeByUuid($params)
    {
        $programme = FundingProgramme::isUuid($params['programme_uuid'])->first();

        if (! $programme) {
            $this->error("Programme with uuid {$programme->uuid} not found");

            return;
        }

        $organisations = $programme->organisations;

        $organisationTypes = $params['organisation_types'];

        foreach ($organisationTypes as $organisationType => $fields) {
            Log::info("Processing organisation type {$organisationType}");
            $organisations = $organisations->where('type', $organisationType);

            Log::info("Found {$organisations->count()} organisations");

            $organisations->each(function (Organisation $organisation) use ($fields) {

                //remove label from $fields
                $label = $fields['label'];
                unset($fields['label']);

                foreach ($fields as $field => $year) {
                    $value = $organisation[$field];
                    $this->safeUpdateOrCreate($organisation->id, $year, $label, $value);
                }

            });
        }

    }

    public function safeUpdateOrCreate($orgId, $year, $collection, $amount)
    {
        Log::info("Updating organisation {$orgId} with year {$year} and collection {$collection} and amount {$amount}");
        $where = [
            'organisation_id' => $orgId,
            'year' => $year,
            'collection' => $collection,
        ];

        return FinancialIndicators::updateOrCreate($where, ['amount' => $amount ?? 0]);
    }
}
