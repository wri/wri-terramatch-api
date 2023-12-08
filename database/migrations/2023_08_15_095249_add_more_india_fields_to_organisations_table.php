<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreIndiaFieldsToOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->text('engagement_non_youth')->nullable();
            $table->text('tree_restoration_practices')->nullable();
            $table->text('business_model')->nullable();
            $table->text('subtype')->nullable();
            $table->bigInteger('organisation_revenue_this_year')->nullable();
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
                'engagement_non_youth',
                'tree_restoration_practices',
                'organisation_revenue_this_year',
                'business_model',
                'subtype',
            ]);
        });
    }
}
