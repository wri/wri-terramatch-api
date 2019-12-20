<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('organisation_id')->unsigned();
            $table->enum('type',['offers','pitches']);
            $table->boolean('land_types')->default(false);
            $table->boolean('land_ownerships')->default(false);
            $table->boolean('land_size')->default(false);
            $table->boolean('land_continent')->default(false);
            $table->boolean('land_country')->default(false);
            $table->boolean('restoration_methods')->default(false);
            $table->boolean('restoration_goals')->default(false);
            $table->boolean('funding_sources')->default(false);
            $table->boolean('funding_amount')->default(false);
            $table->boolean('long_term_engagement')->default(false);
            $table->boolean('reporting_frequency')->default(false);
            $table->boolean('reporting_level')->default(false);
            $table->boolean('sustainable_development_goals')->default(false);
            $table->boolean('price_per_tree')->default(false);
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("organisation_id")->references("id")->on("organisations");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filter_records');
    }
}
