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
            $table->text('resilience_progress');
            $table->text('local_governance');
            $table->text('adaptive_management');
            $table->text('scalability_replicability');
            $table->text('convergence_jobs_description');
            $table->text('convergence_schemes');
            $table->unsignedInteger('convergence_amount');
            $table->text('community_partners_assets_description');
            $table->unsignedInteger('volunteer_scstobc');
            $table->unsignedInteger('beneficiaries_scstobc_farmers');
            $table->unsignedInteger('beneficiaries_scstobc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('resilience_progress');
            $table->dropColumn('local_governance');
            $table->dropColumn('adaptive_management');
            $table->dropColumn('scalability_replicability');
            $table->dropColumn('convergence_jobs_description');
            $table->dropColumn('convergence_schemes');
            $table->dropColumn('convergence_amount');
            $table->dropColumn('community_partners_assets_description');
            $table->dropColumn('volunteer_scstobc');
            $table->dropColumn('beneficiaries_scstobc_farmers');
            $table->dropColumn('beneficiaries_scstobc');
        });
    }
};
