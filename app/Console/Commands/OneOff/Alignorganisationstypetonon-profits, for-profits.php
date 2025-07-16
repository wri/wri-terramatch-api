<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Organisation;
use Illuminate\Console\Command;

class AlignOrganisationsTypeToNonProfitsForProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:align-organisations-type-to-non-profits-for-profits 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aligns organisation types to standard types: non-profit-organization, for-profit-organization, and government-agency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Aligning organisation types to standard types...');

        // Define the mapping of old types to new types
        $typeMapping = [
            // Non-profit types
            'non-profit-organisation' => Organisation::TYPE_NON_PROFIT,
            'ngo' => Organisation::TYPE_NON_PROFIT,
            'international_ngo' => Organisation::TYPE_NON_PROFIT,
            'nonprofit' => Organisation::TYPE_NON_PROFIT,
            'foundation' => Organisation::TYPE_NON_PROFIT,
            
            // For-profit types
            'llc' => Organisation::TYPE_FOR_PROFIT,
            'b_corporation' => Organisation::TYPE_FOR_PROFIT,
            'corporation' => Organisation::TYPE_FOR_PROFIT,
            
            // Government types
            'government' => Organisation::TYPE_GOVERNMENT,
            
            // Other types (se mantiene como 'other')
            'other' => 'other',
        ];

        // Get organisations that need to be updated
        $organisationsToUpdate = Organisation::whereIn('type', array_keys($typeMapping))
            ->orWhereNull('type')
            ->get(['id', 'uuid', 'name', 'type']);

        if ($organisationsToUpdate->isEmpty()) {
            $this->info('No organisations found that need type alignment.');
            return 0;
        }

        $this->info("Found {$organisationsToUpdate->count()} organisations that need type alignment:");

        // Group by current type for display
        $groupedByType = $organisationsToUpdate->groupBy('type');
        foreach ($groupedByType as $currentType => $organisations) {
            $newType = $typeMapping[$currentType] ?? 'NULL';
            $this->line("   - '{$currentType}' → '{$newType}': {$organisations->count()} organisations");
        }

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made. Here are the conversions that would happen:');
            foreach ($organisationsToUpdate as $organisation) {
                $newType = $typeMapping[$organisation->type] ?? null;
                $newTypeDisplay = $newType ?? 'NULL';
                $this->line("   Organisation ID {$organisation->id} ('{$organisation->name}'): '{$organisation->type}' → '{$newTypeDisplay}'");
            }
            return 0;
        }

        if (!$force && !$this->confirm('Do you want to proceed with the type alignment?')) {
            $this->info('Operation cancelled.');
            return 1;
        }

        $this->info('Starting type alignment...');

        $successCount = 0;
        $errorCount = 0;

        foreach ($organisationsToUpdate as $organisation) {
            try {
                $newType = $typeMapping[$organisation->type] ?? null;
                $oldType = $organisation->type ?? 'NULL';
                $newTypeDisplay = $newType ?? 'NULL';

                $organisation->update(['type' => $newType]);

                $successCount++;
                $this->line("   ✅ Organisation ID {$organisation->id}: '{$oldType}' → '{$newTypeDisplay}'");
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("   ❌ Failed to update Organisation ID {$organisation->id}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        if ($successCount > 0) {
            $this->info("Successfully aligned {$successCount} organisations.");
        }
        if ($errorCount > 0) {
            $this->error("Failed to align {$errorCount} organisations.");
        }

        $this->info('Type alignment completed!');

        return $errorCount > 0 ? 1 : 0;
    }
}
