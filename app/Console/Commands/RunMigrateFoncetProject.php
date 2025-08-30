<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;

class RunMigrateFoncetProject extends Command
{
    // php artisan app:migrate-foncet-project migrate

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-foncet-project {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('operation');
        $this->info("Hello, {$name}! This is your custom command 🚀");

        $project = Project::where('uuid', '3ca98137-ad7a-4849-bdf9-f1e6ccdfb40f')->first();
        if (! $project) {
            $this->error('Project not found.');

            return Command::FAILURE;
        }

    }
}
