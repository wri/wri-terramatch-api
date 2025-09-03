<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('disturbance_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disturbanceable_type');
            $table->unsignedBigInteger('disturbanceable_id');
            $table->date('disturbance_date')->nullable();
            $table->string('collection')->nullable();
            $table->string('type')->nullable();
            $table->string('subtype')->nullable();
            $table->string('intensity')->nullable();
            $table->string('extent')->nullable();
            $table->unsignedInteger('people_affected')->nullable();
            $table->decimal('monetary_damage', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('action_description')->nullable();
            $table->string('property_affected')->nullable();
            $table->unsignedInteger('old_id')->nullable();
            $table->string('old_model')->nullable();
            $table->boolean('hidden')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('disturbance_reports');
    }
};
