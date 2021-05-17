<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetsTable extends Migration
{
    public function up()
    {
        Schema::create("targets", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("monitoring_id");
            $table->enum("negotiator", ["offer", "pitch"]);
            $table->date("start_date");
            $table->date("finish_date");
            $table->integer("funding_amount");
            $table->json("land_geojson");
            $table->integer("trees_planted")->nullable();
            $table->integer("non_trees_planted")->nullable();
            $table->integer("survival_rate")->nullable();
            $table->integer("land_size_planted")->nullable();
            $table->integer("land_size_restored")->nullable();
            $table->integer("carbon_captured")->nullable();
            $table->integer("supported_nurseries")->nullable();
            $table->integer("nurseries_production_amount")->nullable();
            $table->integer("short_term_jobs_amount")->nullable();
            $table->integer("long_term_jobs_amount")->nullable();
            $table->integer("volunteers_amount")->nullable();
            $table->integer("training_amount")->nullable();
            $table->integer("benefited_people")->nullable();
            $table->timestamps();
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedBigInteger("created_by");
            $table->unsignedBigInteger("accepted_by")->nullable();

            $table->foreign("created_by")->references("id")->on("users");
            $table->foreign("accepted_by")->references("id")->on("users");
            $table->foreign("monitoring_id")->references("id")->on("monitorings");
        });
    }

    public function down()
    {
    }
}
