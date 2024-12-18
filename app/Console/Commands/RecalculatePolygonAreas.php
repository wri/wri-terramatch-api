<?php

namespace App\Console\Commands;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Services\AreaCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculatePolygonAreas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polygons:recalculate-areas 
                            {--batch-size=100 : Number of records to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate areas for all site polygons';

    /**
     * Execute the console command.
     */
    public function handle(AreaCalculationService $areaService)
    {
        DB::beginTransaction();

        try {
            $sitePolygons = SitePolygon::query()->cursor();

            $processedCount = 0;
            $errorCount = 0;

            $this->info('Starting polygon area recalculation...');
            $progressBar = $this->output->createProgressBar();
            $progressBar->start();

            foreach ($sitePolygons as $sitePolygon) {
                try {
                    $polygonGeometry = PolygonGeometry::where('uuid', $sitePolygon->poly_id)
                    ->select('uuid', DB::raw('ST_AsGeoJSON(geom) AS geojsonGeometry'))
                    ->first();
                    if (! $polygonGeometry) {
                        $this->error("No geometry found for poly_id: {$sitePolygon->poly_id}");
                        $errorCount++;

                        continue;
                    }
                    $geometry = json_decode($polygonGeometry->geojsonGeometry, true);

                    $calculatedArea = $areaService->getArea($geometry);

                    $sitePolygon->calc_area = $calculatedArea;
                    $sitePolygon->save();

                    $processedCount++;
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $this->error("Error processing polygon {$sitePolygon->id}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::commit();

            $progressBar->finish();
            $this->info("\n\nRecalculation complete!");
            $this->info("Processed: {$processedCount} polygons");
            $this->info("Errors: {$errorCount}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Recalculation failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
