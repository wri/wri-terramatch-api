<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Console\Command;

class DeleteTerrafundProgrammeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-terrafund-programme {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a Terrafund programme and all related data';

    public function handle(): int
    {
        $terrafundProgramme = TerrafundProgramme::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteTerrafundProgrammeAndRelations($terrafundProgramme);

        $this->info("Programme $terrafundProgramme->name ($terrafundProgramme->id) Deleted");

        return 0;
    }
}
