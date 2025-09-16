<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Console\Command;

class RemoveLeadershipsDataInFormQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:remove-leaderships-data-form-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove leaderships data from form_questions table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data migration...');

        $this->removeLeaderships();

        $this->info('Data migration completed successfully!');
    }

    private function removeLeaderships()
    {
        $leaderFormQuestions = FormQuestion::whereIn('linked_field_key', ['org-leadership-team', 'org-core-team-leaders'])
            ->get();

        foreach ($leaderFormQuestions as $record) {
            $record->delete();
            $this->info("Soft deleted record {$record->id} from form_questions.");
        }
    }
}
