<?php

use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2PolygonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_polygons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('name')->nullable();
            $table->float('area')->nullable();
            $table->float('perimeter')->nullable();
            $table->foreignIdFor(User::class, 'owner_id')->nullable();
            $table->string('status')->nullable();
            $table->morphs('polygonable');
            // add restorationStrat when it's migration is complete
            // add landUseTypes when it's migration is complete
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_polygons');
    }
}
