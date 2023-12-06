<?php

use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ProjectReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_project_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Project::class)->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'approved_by')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->text('title')->nullable();
            $table->text('technical_narrative')->nullable();
            $table->text('public_narrative')->nullable();
            $table->text('landscape_community_contribution')->nullable();
            $table->text('top_three_successes')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('maintenance_and_monitoring_activities')->nullable();
            $table->text('significant_change')->nullable();
            $table->text('survival_calculation')->nullable();
            $table->text('survival_comparison')->nullable();
            $table->text('volunteers_work_description')->nullable();
            $table->text('new_jobs_description')->nullable();
            $table->text('beneficiaries_description')->nullable();
            $table->text('beneficiaries_income_increase_description')->nullable();
            $table->text('beneficiaries_skills_knowledge_increase_description')->nullable();
            $table->unsignedInteger('workdays_paid')->nullable();
            $table->unsignedInteger('workdays_volunteer')->nullable();
            $table->unsignedTinyInteger('pct_survival_to_date')->nullable();
            $table->unsignedInteger('ft_women')->nullable();
            $table->unsignedInteger('ft_men')->nullable();
            $table->unsignedInteger('ft_youth')->nullable();
            $table->unsignedInteger('ft_smallholder_farmers')->nullable();
            $table->unsignedInteger('ft_total')->nullable();
            $table->unsignedInteger('pt_women')->nullable();
            $table->unsignedInteger('pt_men')->nullable();
            $table->unsignedInteger('pt_youth')->nullable();
            $table->unsignedInteger('pt_smallholder_farmers')->nullable();
            $table->unsignedInteger('pt_total')->nullable();
            $table->unsignedInteger('seasonal_women')->nullable();
            $table->unsignedInteger('seasonal_men')->nullable();
            $table->unsignedInteger('seasonal_youth')->nullable();
            $table->unsignedInteger('seasonal_smallholder_farmers')->nullable();
            $table->unsignedInteger('seasonal_total')->nullable();
            $table->unsignedInteger('volunteer_women')->nullable();
            $table->unsignedInteger('volunteer_men')->nullable();
            $table->unsignedInteger('volunteer_youth')->nullable();
            $table->unsignedInteger('volunteer_smallholder_farmers')->nullable();
            $table->unsignedInteger('volunteer_total')->nullable();
            $table->unsignedInteger('planted_trees')->nullable();
            $table->unsignedInteger('new_jobs_created')->nullable();
            $table->unsignedInteger('new_volunteers')->nullable();
            $table->unsignedInteger('ft_jobs_non_youth')->nullable();
            $table->unsignedInteger('ft_jobs_youth')->nullable();
            $table->unsignedInteger('volunteer_non_youth')->nullable();
            $table->unsignedInteger('beneficiaries')->nullable();
            $table->unsignedInteger('beneficiaries_women')->nullable();
            $table->unsignedInteger('beneficiaries_men')->nullable();
            $table->unsignedInteger('beneficiaries_non_youth')->nullable();
            $table->unsignedInteger('beneficiaries_youth')->nullable();
            $table->unsignedInteger('beneficiaries_smallholder')->nullable();
            $table->unsignedInteger('beneficiaries_large_scale')->nullable();
            $table->unsignedInteger('beneficiaries_income_increase')->nullable();
            $table->unsignedInteger('beneficiaries_skills_knowledge_increase')->nullable();
            $table->string('shared_drive_link')->nullable();

            $table->unsignedInteger('old_id')->nullable();
            $table->string('old_model')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_project_reports');
    }
}
