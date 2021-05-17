<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressUpdatesTable extends Migration
{
    public function up()
    {
        Schema::create("progress_updates", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("monitoring_id");
            $table->enum("grouping", ["general", "planting", "monitoring"]);
            $table->string("title");
            $table->text("breakdown");
            $table->text("summary");
            $table->json("data");
            $table->json("images");
            $table->unsignedBigInteger("created_by");
            $table->timestamps();
            $table->foreign("monitoring_id")->references("id")->on("monitorings");
            $table->foreign("created_by")->references("id")->on("users");
        });
    }

    public function down()
    {
        Schema::dropIfExists("progress_updates");
    }
}
