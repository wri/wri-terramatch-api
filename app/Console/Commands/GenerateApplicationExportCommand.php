<?php

namespace App\Console\Commands;

use App\Jobs\GenerateApplicationExportJob;
use App\Models\V2\FundingProgramme;
use Illuminate\Console\Command;

class GenerateApplicationExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-application-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an application export';

    public function handle(): int
    {
        FundingProgramme::query()
            ->each(function (FundingProgramme $fundingProgramme) {
                $this->info('generating for ' . $fundingProgramme->id);
                GenerateApplicationExportJob::dispatch($fundingProgramme);
            });

        return 0;
    }
}
