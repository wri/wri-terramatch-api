<?php

namespace App\Console\Commands;

use App\Helpers\DeletionHelper;
use App\Models\Terrafund\TerrafundNursery;
use Illuminate\Console\Command;

class DeleteTerrafundNurseryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-terrafund-nursery {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a Terrafund nursery and all related data';

    public function handle(): int
    {
        $nursery = TerrafundNursery::where('id', $this->argument('id'))->firstOrFail();

        DeletionHelper::deleteTerrafundNurseryAndRelations($nursery);

        $this->info("Nursery $nursery->name ($nursery->id) Deleted");

        return 0;
    }
}
