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
        Schema::table('v2_projects', function (Blueprint $table): void {
            $table->text('level_1_project')->nullable();
            $table->text('level_2_project')->nullable();
            $table->text('land_tenure_approach')->nullable();
            $table->string('seedlings_procurement', 255)->nullable();
            $table->text('jobs_goal_description')->nullable();
            $table->text('volunteers_goal_description')->nullable();
            $table->text('community_engagement_plan')->nullable();
            $table->text('direct_beneficiaries_goal_description')->nullable();
            $table->tinyInteger('elp_project')->default(0);
            $table->text('consortium')->nullable();
            $table->string('landowner_agreement', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_projects', function (Blueprint $table): void {
            $table->dropColumn([
                'level_1_project',
                'level_2_project',
                'land_tenure_approach',
                'seedlings_procurement',
                'jobs_goal_description',
                'volunteers_goal_description',
                'community_engagement_plan',
                'direct_beneficiaries_goal_description',
                'elp_project',
                'consortium',
                'landowner_agreement',
            ]);
        });
    }
};
