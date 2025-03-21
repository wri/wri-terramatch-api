<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('financial_indicators', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->char('organisation_id', 36);
            $table->string('financial_collection_type', 20);
            $table->integer('amount');
            $table->year('year');
            $table->string('documentation')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_indicators');
    }
};
