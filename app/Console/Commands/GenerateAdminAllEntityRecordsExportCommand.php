<?php

namespace App\Console\Commands;

use App\Jobs\V2\GenerateAdminAllEntityRecordsExportJob;
use Illuminate\Console\Command;

class GenerateAdminAllEntityRecordsExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-admin-all-entity-records-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate export with all the entity records in database';

    public function handle(): int
    {
        $entities = ['projects', 'sites', 'nurseries', 'project-reports', 'site-reports', 'nursery-reports'];
        $frameworks = ['ppc', 'terrafund'];

        foreach ($entities as $entity) {
            foreach ($frameworks as $framework) {
                GenerateAdminAllEntityRecordsExportJob::dispatch($entity, $framework);
            }
        }

        return 0;
    }
}
