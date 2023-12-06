<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2FundingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_funding_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignUuid('organisation_id');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('year');
            $table->text('type');
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
        Schema::dropIfExists('v2_funding_types');
    }
}
