<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToTerrafundProgrammeSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            // removed columns
            $table->unsignedInteger('ft_smallholder_farmers')->nullable()->change();
            $table->unsignedInteger('pt_smallholder_farmers')->nullable()->change();
            $table->unsignedInteger('volunteer_smallholder_farmers')->nullable()->change();
            $table->unsignedInteger('seasonal_men')->nullable()->change();
            $table->unsignedInteger('seasonal_smallholder_farmers')->nullable()->change();
            $table->unsignedInteger('seasonal_total')->nullable()->change();
            $table->unsignedInteger('seasonal_women')->nullable()->change();
            $table->unsignedInteger('seasonal_youth')->nullable()->change();

            // new columns
            $table->text('challenges_faced')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->boolean('planted_trees')->nullable();
            $table->unsignedInteger('new_jobs_created')->nullable();
            $table->text('new_jobs_description')->nullable();
            $table->unsignedInteger('new_volunteers')->nullable();
            $table->text('volunteers_work_description')->nullable();
            $table->unsignedInteger('full_time_jobs_35plus')->nullable();
            $table->unsignedInteger('part_time_jobs_35plus')->nullable();
            $table->unsignedInteger('volunteer_35plus')->nullable();
            $table->unsignedInteger('beneficiaries')->nullable();
            $table->text('beneficiaries_description')->nullable();
            $table->unsignedInteger('women_beneficiaries')->nullable();
            $table->unsignedInteger('men_beneficiaries')->nullable();
            $table->unsignedInteger('beneficiaries_35plus')->nullable();
            $table->unsignedInteger('youth_beneficiaries')->nullable();
            $table->unsignedInteger('smallholder_beneficiaries')->nullable();
            $table->unsignedInteger('large_scale_beneficiaries')->nullable();
            $table->unsignedInteger('beneficiaries_income_increase')->nullable();
            $table->text('income_increase_description')->nullable();
            $table->text('beneficiaries_skills_knowledge_increase')->nullable();
            $table->text('skills_knowledge_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'challenges_faced',
                'lessons_learned',
                'planted_trees',
                'new_jobs_created',
                'new_jobs_description',
                'new_volunteers',
                'volunteers_work_description',
                'full_time_jobs_35plus',
                'part_time_jobs_35plus',
                'volunteer_35plus',
                'beneficiaries',
                'beneficiaries_description',
                'women_beneficiaries',
                'men_beneficiaries',
                'beneficiaries_35plus',
                'youth_beneficiaries',
                'smallholder_beneficiaries',
                'large_scale_beneficiaries',
                'beneficiaries_income_increase',
                'income_increase_description',
                'beneficiaries_skills_knowledge_increase',
                'skills_knowledge_description',
            ]);
        });
    }
}
