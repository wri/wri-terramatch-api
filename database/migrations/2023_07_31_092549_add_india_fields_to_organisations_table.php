<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndiaFieldsToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('states')->nullable();
            $table->text('district')->nullable();
            $table->text('account_number_1')->nullable();
            $table->text('account_number_2')->nullable();
            $table->text('loan_status_amount')->nullable();
            $table->text('loan_status_types')->nullable();
            $table->text('approach_of_marginalized_communities')->nullable();
            $table->text('community_engagement_numbers_marginalized')->nullable();
            $table->text('land_systems')->nullable();
            $table->text('fund_utilisation')->nullable();
            $table->text('detailed_intervention_types')->nullable();

            $table->integer('community_members_engaged_3yr')->nullable();
            $table->integer('community_members_engaged_3yr_women')->nullable();
            $table->integer('community_members_engaged_3yr_men')->nullable();
            $table->integer('community_members_engaged_3yr_youth')->nullable();
            $table->integer('community_members_engaged_3yr_non_youth')->nullable();
            $table->integer('community_members_engaged_3yr_smallholder')->nullable();
            $table->integer('community_members_engaged_3yr_backward_class')->nullable();

            $table->integer('total_board_members')->nullable();
            $table->integer('pct_board_women')->nullable();
            $table->integer('pct_board_men')->nullable();
            $table->integer('pct_board_youth')->nullable();
            $table->integer('pct_board_non_youth')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'states',
                'district',
                'account_number_1',
                'account_number_2',
                'loan_status_amount',
                'loan_status_types',
                'approach_of_marginalized_communities',
                'community_engagement_numbers_marginalized',
                'land_systems',
                'fund_utilisation',
                'community_members_engaged_3yr',
                'community_members_engaged_3yr_women',
                'community_members_engaged_3yr_men',
                'community_members_engaged_3yr_youth',
                'community_members_engaged_3yr_non_youth',
                'community_members_engaged_3yr_smallholder',
                'community_members_engaged_3yr_backward_class',
            ]);
        });
    }
}
