<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLeadershipsDataInFormQuestions extends Command
{
    protected $signature = 'update:leaderships-data-form-questions';
    protected $description = 'Update data from form_questions table';

    public function handle()
    {
        $this->info('Starting data migration...');

        $this->updateLeaderships();

        $this->info('Data migration completed successfully!');
    }

    private function updateLeaderships()
    {
        $leaderFormQuestions = DB::table('form_questions')
            ->whereIn('linked_field_key', ['org-leadership-team', 'org-core-team-leaders'])
            ->get();

        foreach ($leaderFormQuestions as $record) {
            $newCollection = '';
            $record->input_type = 'leaderships';

            if ($record->linked_field_key == 'org-leadership-team') {
                $newCollection = 'leadership-team';
            } elseif ($record->linked_field_key == 'org-core-team-leaders') {
                $newCollection = 'core-team-leaders';
            }

            DB::table('form_questions')
                ->where('id', $record->id)
                ->update([
                    'input_type' => 'leaderships',
                    'collection' => $newCollection,
                ]);

            $this->info("updated record {$record->id} from form_questions.");
        }
    }
}
