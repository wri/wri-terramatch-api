<?php

namespace App\Console\Commands;

use App\Models\Programme;
use App\Models\ProgrammeTreeSpecies;
use App\Models\Submission;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportProgrammeSubmissionCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-programme-submission-csv {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a CSV of programme submissions';

    public function handle(): int
    {
        $csv = Reader::createFromPath(base_path('imports/') . $this->argument('filename'), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        foreach ($records as $index => $record) {
            // find the programme - if it doesn't exist we'll skip this entry
            $programme = Programme::where('id', $record['programme identifier'])
                ->first();
            if (! $programme) {
                $this->warn('Skipping index ' . $index . ' - there is no programme found.');

                continue;
            }

            $submission = new Submission();
            $submission->programme_id = $programme->id;
            $submission->title = $record['title'];
            $submission->technical_narrative = $record['technical narrative'];
            $submission->public_narrative = $record['public narrative'];
            $submission->created_at = $record['date'];
            $submission->saveOrFail();

            if (strpos($record['tree species - name'], '//')) {
                $treeSpeciesNames = explode('//', $record['tree species - name']);
                $treeSpeciesAmounts = explode('//', $record['tree species - amount']);

                foreach ($treeSpeciesNames as $index => $treeSpeciesName) {
                    $species = new ProgrammeTreeSpecies();
                    $species->programme_id = $programme->id;
                    $species->programme_submission_id = $submission->id;
                    $species->name = $treeSpeciesName;
                    $species->amount = $treeSpeciesAmounts[$index];
                    $species->saveOrFail();
                }
            }

            $this->info('Programme submission for programme ' . $record['programme identifier'] . ' completed');
        }

        return 0;
    }
}
