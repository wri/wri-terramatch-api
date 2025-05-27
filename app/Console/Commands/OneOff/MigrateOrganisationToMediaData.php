<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateOrganisationToMediaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-organisation-to-media-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate organisation to media data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $haritBharatEnterprisesProgrammeUuid = '86b3ea32-8541-4525-b342-2d8010b3cdf7';

        $revenueLabel = 'revenue';
        $budgetLabel = 'budget';

        $programme = FundingProgramme::isUuid($haritBharatEnterprisesProgrammeUuid)->first();
        $organisations = $programme->organisations;
        $forProfitOrganisations = $organisations->where('type', 'for-profit-organization');
        $nonProfitOrganisations = $organisations->where('type', 'non-profit-organization');

        $this->processOrganisations($forProfitOrganisations, $revenueLabel);
        $this->processOrganisations($nonProfitOrganisations, $budgetLabel);
    }

    private function processOrganisations($organisations, $label)
    {

        $map = [
            'op_budget_next_year' => 2023,
            'op_budget_1year' => 2022,
            'op_budget_2year' => 2021,
            'op_budget_3year' => 2020,
        ];

        $organisations->each(function (Organisation $organisation) use ($label, $map) {
            foreach ($map as $key => $year) {
                $this->safeUpdate($organisation->id, $label, $key, $year);
            }
        });
    }

    public function safeUpdate($orgId, $label, $key, $year)
    {
        $medias = Media::where('model_type', 'App\\Models\\V2\\Organisation')->where('model_id', $orgId)->where('collection_name', $key)->get();
        $medias->each(function (Media $media) use ($year, $key, $label, $orgId) {
            $existing = FinancialIndicators::where([
                'organisation_id' => $orgId,
                'year' => $year,
                'collection' => 'description-documents',
            ])->first();

            if (! $existing) {
                $existing = FinancialIndicators::create([
                    'organisation_id' => $orgId,
                    'year' => $year,
                    'collection' => 'description-documents',
                ]);
            }

            $media->model_type = 'App\\Models\\V2\\FinancialIndicators';
            $media->model_id = $existing->id;
            $media->collection_name = 'documentation';
            $media->save();
        });
    }
}
