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
        //
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('bioeconomy_product_list')->nullable();
            $table->text('bioeconomy_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('bioeconomy_product_list');
            $table->dropColumn('bioeconomy_description');
        });
    }
};
