<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSitePolygonCentroidsCommand extends Command
{
    protected $signature = 'site-polygons:update-centroids 
                            {--batch-size=1000 : Number of records to process per batch}';

    protected $description = 'Update lat/long fields for site polygons by calculating centroids';
    

    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');

        $totalCount = SitePolygon::whereNull('deleted_at')
            ->count();

        if ($totalCount === 0) {
            $this->info('No records to process');

            return 0;
        }

        $this->info("Processing {$totalCount} site polygons in batches of {$batchSize}");

        $bar = $this->output->createProgressBar($totalCount);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $processedCount = 0;
        $errorCount = 0;
        $currentId = 0;

        while (true) {
            $batch = DB::table('site_polygon as sp')
                ->join('polygon_geometry as pg', 'sp.poly_id', '=', 'pg.uuid')
                ->select([
                    'sp.id',
                    DB::raw('ST_Y(ST_Centroid(pg.geom)) as lat'),
                    DB::raw('ST_X(ST_Centroid(pg.geom)) as `long`')
                ])
                ->whereNull('sp.deleted_at')
                ->where('sp.id', '>', $currentId)
                ->orderBy('sp.id')
                ->limit($batchSize)
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            try {
                DB::beginTransaction();

                foreach ($batch as $record) {
                    if ($this->isValidCoordinate($record->lat, $record->long)) {
                        DB::table('site_polygon')
                            ->where('id', $record->id)
                            ->update([
                                'lat' => $record->lat,
                                'long' => $record->long
                            ]);
                        $processedCount++;
                    } else {
                        $errorCount++;
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error processing batch: ' . $e->getMessage());
                $errorCount += $batch->count();
            }

            $bar->advance($batch->count());
            $currentId = $batch->last()->id;
        }

        $bar->finish();
        $this->newLine();

        $this->info("Completed! Processed: {$processedCount}, Errors: {$errorCount}");

        return $errorCount > 0 ? 1 : 0;
    }

    private function isValidCoordinate($lat, $long): bool
    
    {
        return is_numeric($lat) && is_numeric($long)
        
            && $lat >= -90 && $lat <= 90
            && $long >= -180 && $long <= 180;
            
            
    }
}
