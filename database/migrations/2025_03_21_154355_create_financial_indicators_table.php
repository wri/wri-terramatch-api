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
          $table->unsignedBigInteger('organisation_id');
          $table->string('collection', 255);
          $table->decimal('amount', 15, 2);
          $table->tinyInteger('year')->unsigned();
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
