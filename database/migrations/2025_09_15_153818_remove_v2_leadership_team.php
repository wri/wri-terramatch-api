<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('v2_leadership_team');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('v2_leadership_team', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->char('uuid', 36)->unique();
            $table->char('organisation_id', 36)->index();
            $table->text('position');
            $table->text('gender');
            $table->unsignedTinyInteger('age');
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
