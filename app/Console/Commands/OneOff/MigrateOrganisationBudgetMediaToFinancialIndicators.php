<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateOrganisationBudgetMediaToFinancialIndicators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-organisation-budget-media-to-financial-indicators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate media files from Organisation to FinancialIndicators by year based on collection_name';

    /**
     * Execute the console command.
     */
    protected array $collectionNames = [
        'op_budget_next_year',
        'op_budget_1year',
        'op_budget_2year',
        'op_budget_3year',
    ];

    public function handle(): void
    {
        Organisation::with(['financialCollection' => fn ($q) => $q->orderBy('year', 'desc')])
            ->chunk(100, function ($organisations) {
                foreach ($organisations as $organisation) {
                    $this->migrateOrganisationMedia($organisation);
                }
            });

        $this->info('Migration completed!!!!!');
    }

    private function migrateOrganisationMedia(Organisation $organisation): void
    {
        $existingMediaCollections = Media::where('model_type', Organisation::class)
            ->where('model_id', $organisation->id)
            ->whereIn('collection_name', $this->collectionNames)
            ->pluck('collection_name')
            ->unique()
            ->sortBy(fn ($c) => array_search($c, $this->collectionNames))
            ->values();

        if ($existingMediaCollections->isEmpty()) {
            return;
        }

        $existingIndicators = FinancialIndicators::where('organisation_id', $organisation->id)
            ->orderBy('year')
            ->get();

        $indicatorYears = $existingIndicators->pluck('year')->unique()->sort()->values();

        if ($indicatorYears->isEmpty()) {
            $startYear = Carbon::now()->year - 1;
            foreach ($existingMediaCollections as $i => $collection) {
                $newIndicator = FinancialIndicators::create([
                    'organisation_id' => $organisation->id,
                    'year' => $startYear - (count($existingMediaCollections) - 1 - $i),
                    'collection' => FinancialIndicators::COLLECTION_NOT_COLLECTION_DOCUMENTS,
                ]);
                $existingIndicators->push($newIndicator);
            }

            $existingIndicators = $existingIndicators->sortBy('year')->values();
            $indicatorYears = $existingIndicators->pluck('year')->unique()->sort()->values();
            $this->info("Created indicators for org: {$organisation->id}");
        }

        $collectionOrder = [
            'op_budget_3year',
            'op_budget_2year',
            'op_budget_1year',
            'op_budget_next_year',
        ];

        $yearMap = [];
        $yearIndex = 0;

        foreach ($collectionOrder as $collection) {
            if ($existingMediaCollections->contains($collection)) {
                $yearMap[$collection] = $indicatorYears->get($yearIndex++);
            }
        }

        foreach ($yearMap as $collection => $year) {
            if (! $year) {
                continue;
            }

            $mediaItems = Media::where('model_type', Organisation::class)
                ->where('model_id', $organisation->id)
                ->where('collection_name', $collection)
                ->get();

            if ($mediaItems->isEmpty()) {
                continue;
            }

            $indicator = $existingIndicators->firstWhere(fn ($fi) => $fi->year == $year && $fi->collection === 'description-documents');

            if (! $indicator) {
                $indicator = FinancialIndicators::create([
                    'organisation_id' => $organisation->id,
                    'year' => $year,
                    'collection' => 'description-documents',
                ]);
                $this->info("Created indicator for org {$organisation->id} year $year [description-documents]");
            }

            foreach ($mediaItems as $media) {
                $media->model_type = FinancialIndicators::class;
                $media->model_id = $indicator->id;
                $media->collection_name = 'documentation';
                $media->save();

                $this->line("Migrated media ID {$media->id}, COLLECTION_NAME {$media->collection_name} to FinancialIndicator {$indicator->id} ({$year}) - ({$indicator->collection})");
            }
        }
    }
}
