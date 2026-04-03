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
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->text('bioeconomy_product_list')->nullable();
            $table->text('bioeconomy_product_description')->nullable();
        });

        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->text('elp_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('bioeconomy_product_list');
            $table->dropColumn('bioeconomy_product_description');
        });

        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('elp_description');
        });
    }
};
