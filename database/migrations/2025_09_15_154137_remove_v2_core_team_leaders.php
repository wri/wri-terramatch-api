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
        Schema::dropIfExists('v2_core_team_leaders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('v2_core_team_leaders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36)->unique();
            $table->char('organisation_id', 36)->index();
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('position')->nullable();
            $table->text('gender')->nullable();
            $table->text('role')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
