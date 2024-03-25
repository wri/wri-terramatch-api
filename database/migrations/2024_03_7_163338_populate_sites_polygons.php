<?php

use App\Models\V2\Sites\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PopulateSitesPolygons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $sites = Site::all();

      // Populate site_polygons table
      foreach ($sites as $site) {
          $sitePolygonData = [
              'side_id' => $site->uuid,
              'project_id' => $site->project_id,
              // 'project_label' => $site->project_label,
              'site_name' => $site->name,
              // 'poly_label' => $site->name,
              // 'poly_id' => $site->uuid,
              // 'plant_date' => $site->plant_date,
              // 'country' => $site->country,
              // 'org_name' => $site->org_name,
              // 'practice' => $site->practice,
              // 'target_sys' => $site->target_sys,
              // 'dist' => $site->dist,
              // 'tree_count' => $site->tree_count,
              // 'estimated_area' => $site->estimated_area
          ];

          SitePolygon::create($sitePolygonData);
      }

      $this->info('Site polygons populated successfully.');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('site_polygon');
    }
}
// php artisan migrate --path=/database/migrations/2024_03_7_121238_create_sites_polygons.php
