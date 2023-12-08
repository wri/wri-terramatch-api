<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Programme;
use Illuminate\Console\Command;

class DeleteProgrammeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-programme {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a programme and all related data';

    public function handle(): int
    {
        $programme = Programme::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteProgrammeAndRelations($programme);

        $this->info("Programme $programme->name ($programme->id) Deleted");

        return 0;
    }
}
