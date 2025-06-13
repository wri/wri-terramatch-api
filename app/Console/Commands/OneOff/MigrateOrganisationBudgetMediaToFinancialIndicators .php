<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateOrganisationBudgetMediaToFinancialIndicators extends Command
{
    protected $signature = 'one-off:migrate-budget-media-to-financial-indicators';
    protected $description = 'Migrate media files from Organisation to FinancialIndicators by year based on collection_name';

    protected array $collectionNames = [
        'op_budget_next_year',
        'op_budget_1year',
        'op_budget_2year',
        'op_budget_3year',
    ];

    public function handle(): void
    {
        Organisation::with(['financialIndicators' => fn ($q) => $q->orderBy('year', 'desc')])
            ->chunk(100, function ($organisations) {
                foreach ($organisations as $organisation) {
                    $this->migrateOrganisationMedia($organisation);
                }
            });

        $this->info('Migration completed.');
    }

    private function migrateOrganisationMedia(Organisation $organisation): void
    {
        $indicators = $organisation->financialIndicators;

        if ($indicators->isEmpty()) {
            $this->warn("No financial indicators for org: {$organisation->id}");
            return;
        }

        $yearMap = [];

        // Establecer orden de años en base a existencia de op_budget_next_year
        $indicatorYears = $indicators->pluck('year')->unique()->sortDesc()->values();

        if ($indicatorYears->isEmpty()) return;

        $useNextYear = Media::where('model_type', Organisation::class)
            ->where('model_id', $organisation->id)
            ->where('collection_name', 'op_budget_next_year')
            ->exists();

        $yearIndex = 0;
        if ($useNextYear) {
            $yearMap['op_budget_next_year'] = $indicatorYears->get($yearIndex++);
        }

        foreach (['op_budget_1year', 'op_budget_2year', 'op_budget_3year'] as $key) {
            $yearMap[$key] = $indicatorYears->get($yearIndex++);
        }

        foreach ($yearMap as $collection => $year) {
            if (!$year) continue;

            $mediaItems = Media::where('model_type', Organisation::class)
                ->where('model_id', $organisation->id)
                ->where('collection_name', $collection)
                ->get();

            if ($mediaItems->isEmpty()) continue;

            $indicator = $indicators->firstWhere('year', $year);

            if (!$indicator) {
                $this->warn("No indicator found for org: {$organisation->id} year: $year");
                continue;
            }

            foreach ($mediaItems as $media) {
                $media->model_type = FinancialIndicators::class;
                $media->model_id = $indicator->id;
                $media->collection_name = 'documentation';
                $media->save();
                $this->line("Migrated media ID {$media->id} to FinancialIndicator {$indicator->id} ({$year})");
            }
        }
    }
}
