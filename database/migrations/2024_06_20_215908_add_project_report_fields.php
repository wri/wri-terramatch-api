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
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->text('local_engagement_description')->nullable();
            $table->integer('indirect_beneficiaries')->nullable();
            $table->text('indirect_beneficiaries_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('local_engagement_description');
            $table->dropColumn('indirect_beneficiaries');
            $table->dropColumn('indirect_beneficiaries_description');
        });
    }
};
