<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('financial_information', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->char('organisation_id', 36);
            $table->string('currency', 3);
            $table->integer('fin_start_month');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_information');
    }
};
