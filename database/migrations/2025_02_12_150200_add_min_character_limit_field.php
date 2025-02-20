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
        Schema::table('form_questions', function (Blueprint $table) {
            $table->unsignedInteger('min_character_limit')->nullable()->default(0);
            $table->unsignedInteger('max_character_limit')->nullable()->default(90000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropColumn('min_character_limit');
            $table->dropColumn('max_character_limit');
        });
    }
};
