<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('disturbance_report_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('disturbance_report_id');
            $table->string('name');
            $table->string('input_type');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('disturbance_report_entries');
    }
};
