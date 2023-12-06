<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\SiteSubmissionDisturbance;
use App\Models\SiteTreeSpecies;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportSiteSubmissionCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-site-submission-csv {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a CSV of site submissions';

    public function handle(): int
    {
        $csv = Reader::createFromPath(base_path('imports/') . $this->argument('filename'), 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        foreach ($records as $index => $record) {
            // find the site - if it doesn't exist we'll skip this entry
            $site = Site::where('id', $record['site identifier'])
                ->first();
            $this->warn('Skipping index ' . $index . ' - there is no programme found.');
            if (! $site) {
                continue;
            }

            $submission = new SiteSubmission();
            $submission->site_id = $site->id;
            $submission->site_submission_title = $record['title'];
            $submission->created_by = $record['created by'];
            $submission->disturbance_information = $record['disturbance information'];
            $submission->direct_seeding_kg = $record['direct seeding kg'];
            $submission->created_at = $record['date'];
            $submission->saveOrFail();

            $directSeedingNames = explode('//', $record['direct seeding - name']);
            $directSeedingAmounts = explode('//', $record['direct seeding - amount']);
            foreach ($directSeedingNames as $index => $directSeedingName) {
                $submission->directSeedings()->create([
                    'name' => $directSeedingName,
                    'weight' => $directSeedingAmounts[$index],
                    'site_submission_id' => $submission->id,
                ]);
            }

            $treeSpeciesNames = explode('//', $record['tree species - name']);
            $treeSpeciesAmounts = explode('//', $record['tree species - amount']);
            foreach ($treeSpeciesNames as $index => $treeSpeciesName) {
                $species = new SiteTreeSpecies();
                $species->site_id = $site->id;
                $species->site_submission_id = $submission->id;
                $species->name = $treeSpeciesName;
                $species->amount = $treeSpeciesAmounts[$index];
                $species->saveOrFail();
            }

            $disturbanceTypes = explode('//', $record['disturbances - type']);
            $disturbanceIntensities = explode('//', $record['disturbances - intensity']);
            $disturbanceDescriptions = explode('//', $record['disturbances - description']);
            $disturbanceExtents = explode('//', $record['disturbances - extent']);
            foreach ($disturbanceTypes as $index => $disturbanceType) {
                $disturbance = new SiteSubmissionDisturbance();
                $disturbance->site_submission_id = $submission->id;
                $disturbance->disturbance_type = $disturbanceType;
                $disturbance->intensity = $disturbanceIntensities[$index];
                $disturbance->description = $disturbanceDescriptions[$index];
                $disturbance->extent = $disturbanceExtents[$index];
                $disturbance->saveOrFail();
            }
        }

        return 0;
    }
}
