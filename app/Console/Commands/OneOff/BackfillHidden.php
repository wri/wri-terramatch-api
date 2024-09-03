<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillHidden extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:backfill-hidden';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills the \'hidden\' field of report-associated models based on form responses';

    private const REPORT_TYPES = [
        ProjectReport::class,
        SiteReport::class,
        NurseryReport::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;
        foreach (self::REPORT_TYPES as $reportType) {
            $count += $reportType::count();
        }
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $hidden = [];
        foreach (self::REPORT_TYPES as $reportType) {
            $reportType::chunkById(100, function ($reports) use ($bar, &$hidden) {
                foreach ($reports as $report) {
                    $this->findRelationsToHide($report, $hidden);
                    $bar->advance();
                }
            });
        }
        $bar->finish();

        $this->info("\n\nHidden: \n" . json_encode($hidden, JSON_PRETTY_PRINT));
    }

    protected function findRelationsToHide($entity, &$hidden)
    {
        $questions = $entity->getForm()->sections->map(fn ($section) => $section->questions)->flatten();
        $formConfig = $entity->getFormConfig();
        $linkedFieldQuestions = $questions->filter(function ($question) use ($formConfig) {
            $property = data_get($formConfig, "relations.$question->linked_field_key.property");

            return ! empty($property) && ! empty($question->parent_id);
        });

        foreach ($linkedFieldQuestions as $question) {
            if (empty($entity->answers) || $entity->answers[$question->parent_id] !== false) {
                continue;
            }

            $relation = $entity->{$question->input_type}();
            $collection = $question->collection;
            if (! empty($collection)) {
                $relation->where('collection', $collection);
            }

            $count = $relation->count();
            if ($count > 0) {
                $relation->update(['hidden' => true, 'updated_at' => DB::raw('updated_at')]);

                $hidden[] = [
                    'entity_type' => get_class($entity),
                    'entity_uuid' => $entity->uuid,
                    'type' => $question->input_type,
                    'collection' => $collection,
                ];
            }
        }
    }
}
