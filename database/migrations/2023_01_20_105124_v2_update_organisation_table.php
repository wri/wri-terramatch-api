<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->index();
            $table->string('status')->after('uuid')->nullable();

            $table->string('type')->after('status')->nullable();
            $table->string('name')->after('type')->nullable();
            $table->string('phone')->after('name')->nullable();
            $table->text('hq_address')->after('phone')->nullable();
            $table->date('founding_date')->after('hq_address')->nullable();
            $table->text('description')->after('founding_date')->nullable();

            $table->string('web_url')->after('description')->nullable();
            $table->string('facebook_url')->after('web_url')->nullable();
            $table->string('instagram_url')->after('facebook_url')->nullable();
            $table->string('linkedin_url')->after('instagram_url')->nullable();
            $table->string('twitter_url')->after('linkedin_url')->nullable();
            //
            //            $table->integer('fin_start_month')->after('twitter_url')->nullable();
            //            $table->integer('fin_budget_1year')->after('fin_start_month')->nullable();
            //            $table->integer('fin_budget_2year')->after('fin_budget_1year')->nullable();
            //            $table->integer('fin_budget_current_year')->after('fin_budget_2year')->nullable();
            //
            //            $table->integer('ha_restored_total')->after('fin_budget_current_year')->nullable();
            //            $table->integer('ha_restored_3year')->after('ha_restored_total')->nullable();
            //            $table->integer('trees_grown_total')->after('ha_restored_3year')->nullable();
            //            $table->integer('tree_species_grown')->after('trees_grown_total')->nullable();
            //
            //            $table->text('maint_car_approach')->after('tree_species_grown')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
