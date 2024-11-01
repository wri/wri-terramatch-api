<?php

namespace App\Console\Commands;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteDummyBufferedPolygons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:dummy-buffered-polygons {siteId : The UUID of the site to delete polygons for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete dummy buffered polygons for a specified site';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $siteId = $this->argument('siteId');

        DB::beginTransaction();

        try {

            $sitePolygons = SitePolygon::where('site_id', $siteId)->get();

            foreach ($sitePolygons as $sitePolygon) {
                $polygonGeometry = PolygonGeometry::where('uuid', $sitePolygon->poly_id)->first();
                if ($polygonGeometry) {
                    $polygonGeometry->deleteWithRelated();
                }
            }

            DB::commit();

            $this->info("Dummy buffered polygons for site ID $siteId have been successfully deleted.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to delete dummy buffered polygons: ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
