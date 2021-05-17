<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftsTable extends Migration
{
    public function up()
    {
        Schema::create("drafts", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("organisation_id");
            $table->string("name");
            $table->enum("type", ["offer", "pitch"]);
            $table->json("data");
            $table->unsignedBigInteger("created_by");
            $table->unsignedBigInteger("updated_by")->nullable();
            $table->timestamps();
            $table->foreign("organisation_id")->references("id")->on("organisations");
            $table->foreign("created_by")->references("id")->on("users");
            $table->foreign("updated_by")->references("id")->on("users");
        });
    }

    public function down()
    {
    }
}
