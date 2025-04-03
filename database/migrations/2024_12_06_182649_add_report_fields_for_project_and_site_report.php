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
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->longText('business_milestones')->nullable();
            $table->unsignedInteger('ft_other')->nullable();
            $table->unsignedInteger('pt_other')->nullable();
            $table->unsignedInteger('volunteer_other')->nullable();
            $table->unsignedInteger('beneficiaries_other')->nullable();
            $table->unsignedInteger('beneficiaries_training_women')->nullable();
            $table->unsignedInteger('beneficiaries_training_men')->nullable();
            $table->unsignedInteger('beneficiaries_training_other')->nullable();
            $table->unsignedInteger('beneficiaries_training_youth')->nullable();
            $table->unsignedInteger('beneficiaries_training_non_youth')->nullable();
        });

        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->unsignedInteger('pct_survival_to_date')->nullable();
            $table->longText('survival_calculation')->nullable();
            $table->longText('survival_description')->nullable();
            $table->longText('maintenance_activities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('business_milestones');
            $table->dropColumn('ft_other');
            $table->dropColumn('pt_other');
            $table->dropColumn('volunteer_other');
            $table->dropColumn('beneficiaries_other');
            $table->dropColumn('beneficiaries_training_women');
            $table->dropColumn('beneficiaries_training_men');
            $table->dropColumn('beneficiaries_training_other');
            $table->dropColumn('beneficiaries_training_youth');
            $table->dropColumn('beneficiaries_training_non_youth');
        });

        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropColumn('pct_survival_to_date');
            $table->dropColumn('survival_calculation');
            $table->dropColumn('survival_description');
            $table->dropColumn('maintenance_activities');
        });
    }
};
