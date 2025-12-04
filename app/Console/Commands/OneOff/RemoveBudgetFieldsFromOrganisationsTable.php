<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveBudgetFieldsFromOrganisationsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:remove-budget-fields-from-organisations-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove budget fields from organisations table and soft delete related form questions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting removal of budget fields from organisations table...');

        try {
            // Soft delete all form_questions that reference the migrated financial fields
            $this->info('Soft deleting form questions...');
            $this->softDeleteFormQuestions();

            // Remove the columns from organisations table
            $this->info('Removing columns from organisations table...');
            $this->removeColumns();

            $this->info('Budget fields removed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error removing budget fields: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Soft delete form questions that reference the budget fields.
     */
    private function softDeleteFormQuestions(): void
    {
        $linkedFieldKeys = [
            'org-fin-bgt-cur-year',
            'org-fin-bgt-1year',
            'org-fin-bgt-2year',
            'org-fin-bgt-3year',
            'org-rev-this-year',
        ];

        $deletedCount = DB::table('form_questions')
            ->whereIn('linked_field_key', $linkedFieldKeys)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Soft deleted {$deletedCount} form questions.");
    }

    /**
     * Remove the budget columns from organisations table.
     */
    private function removeColumns(): void
    {
        if (! Schema::hasTable('organisations')) {
            $this->warn('Organisations table does not exist. Skipping column removal.');

            return;
        }

        $columnsToRemove = [
            'fin_budget_current_year',
            'fin_budget_1year',
            'fin_budget_2year',
            'fin_budget_3year',
            'organisation_revenue_this_year',
        ];

        $existingColumns = [];
        foreach ($columnsToRemove as $column) {
            if (Schema::hasColumn('organisations', $column)) {
                $existingColumns[] = $column;
            }
        }

        if (empty($existingColumns)) {
            $this->warn('No budget columns found in organisations table. They may have already been removed.');

            return;
        }

        Schema::table('organisations', function ($table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });

        $this->info('Removed columns: ' . implode(', ', $existingColumns));
    }
}
