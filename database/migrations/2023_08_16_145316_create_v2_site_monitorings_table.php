<?php

use App\Models\V2\Sites\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint ;

class CreateV2SiteMonitoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_site_monitorings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Site::class)->nullable();
            $table->string('status')->nullable();
            $table->float('tree_count')->nullable();
            $table->float('tree_cover')->nullable();
            $table->float('field_tree_count')->nullable();
            $table->date('measurement_date')->nullable();
            $table->date('last_updated')->nullable();

            $table->unsignedInteger('old_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_site_monitorings');
    }
}
