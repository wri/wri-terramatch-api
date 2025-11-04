<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeMediaCollectionNames extends Command
{
    protected $signature = 'media:normalize-collections {--dry-run : Show changes without writing}';

    protected $description = 'One-off command: Normalize redundant media.collection_name values to canonical options';

    public function handle(): int
    {
        $normalizations = [
            'soil_water_conservation_photos' => [
                'pattern' => 'soil_or_water_conservation_photos%',
                'target' => 'soil_water_conservation_photos',
            ],
            'soil_water_conservation_upload' => [
                'pattern' => 'soil_or_water_conservation_upload%',
                'target' => 'soil_water_conservation_upload',
            ],
        ];

        $totalAffected = 0;
        $preview = [];

        foreach ($normalizations as $key => $config) {
            $affected = DB::table('media')
                ->whereRaw('collection_name LIKE ?', [$config['pattern']])
                ->where('collection_name', '!=', $config['target'])
                ->count();

            if ($affected > 0) {
                $preview[$key] = [
                    'pattern' => $config['pattern'],
                    'target' => $config['target'],
                    'count' => $affected,
                ];
                $totalAffected += $affected;
            }
        }


        if (empty($preview)) {
            $this->info('No records found that need normalization.');

            return self::SUCCESS;
        }

        $this->line('');
        $this->info('Records to be normalized:');
        $this->line('');
        foreach ($preview as $key => $info) {
            $this->line("  {$info['pattern']} → {$info['target']}: {$info['count']} records");
        }
        $this->line('');
        $this->line("Total: {$totalAffected} records");

        if ($this->option('dry-run')) {
            $this->warn('Dry-run mode: No changes were made.');

            return self::SUCCESS;
        }

        DB::beginTransaction();

        try {
            $actualUpdated = 0;

            foreach ($normalizations as $key => $config) {
                $updated = DB::table('media')
                    ->whereRaw('collection_name LIKE ?', [$config['pattern']])
                    ->where('collection_name', '!=', $config['target'])
                    ->update(['collection_name' => $config['target']]);

                $actualUpdated += $updated;
                $this->line("Updated: {$config['pattern']} → {$config['target']}: {$updated} records");
            }

            DB::commit();
            $this->info("✅ Completed: {$actualUpdated} records normalized successfully");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ Error: {$e->getMessage()}");
            $this->error("Stack trace: {$e->getTraceAsString()}");

            return self::FAILURE;
        }
    }
}
