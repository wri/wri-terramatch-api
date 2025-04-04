<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLeadershipData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:leadership-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy data from old tables to the new leaderships table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data migration...');

        $this->migrateTable('v2_core_team_leaders', 'core-team-leaders');
        $this->migrateTable('v2_leadership_team', 'leadership-team');

        $this->info('Data migration completed successfully!');
    }

    private function migrateTable($oldTable, $collection)
    {
        $oldRecords = DB::table($oldTable)->get();

        foreach ($oldRecords as $record) {
            $organisation = DB::table('organisations')
                ->where('uuid', $record->organisation_id)
                ->first();

            if (! $organisation) {
                $this->warn("Skipping record {$record->id}, organisation not found.");
                continue;
            }

            DB::table('leaderships')->insert([
                'uuid' => $record->uuid,
                'organisation_id' => $organisation->id,
                'collection' => $collection,
                'first_name' => $record->first_name,
                'last_name' => $record->last_name,
                'position' => $record->position,
                'gender' => $record->gender,
                'age' => $record->age,
                //'nationality' => $record->nationality,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);

            $this->info("Migrated record {$record->id} from {$oldTable}.");
        }
    }
}
