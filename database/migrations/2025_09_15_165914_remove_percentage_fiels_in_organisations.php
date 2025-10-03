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

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('pct_board_men');
            $table->dropColumn('pct_board_youth');
            $table->dropColumn('pct_board_non_youth');
            $table->dropColumn('num_of_farmers_on_board');
            $table->dropColumn('pct_board_women');
            $table->dropColumn('total_board_members');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->integer('pct_board_men')->nullable();
            $table->integer('pct_board_youth')->nullable();
            $table->integer('pct_board_non_youth')->nullable();
            $table->integer('num_of_farmers_on_board')->nullable();
            $table->integer('pct_board_women')->nullable();
            $table->integer('total_board_members')->nullable();
        });
    }
};
