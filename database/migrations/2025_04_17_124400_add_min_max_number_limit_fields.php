<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->integer('min_number_limit')->nullable();
            $table->integer('max_number_limit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropColumn('min_number_limit');
            $table->dropColumn('max_number_limit');
        });
    }
};
