<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('hq_address');
            $table->string('hq_country')->after('phone')->nullable();
            $table->string('hq_zipcode')->after('phone')->nullable();
            $table->string('hq_state')->after('phone')->nullable();
            $table->string('hq_city')->after('phone')->nullable();
            $table->string('hq_street_2')->after('phone')->nullable();
            $table->string('hq_street_1')->after('phone')->nullable();
            $table->decimal('fin_budget_3year', 15, 2)->after('description')->nullable();
            $table->decimal('fin_budget_2year', 15, 2)->after('description')->nullable();
            $table->decimal('fin_budget_1year', 15, 2)->after('description')->nullable();
            $table->decimal('fin_budget_current_year', 15, 2)->after('description')->nullable();
            $table->integer('fin_start_month')->after('description')->nullable();
            $table->decimal('ha_restored_total')->after('description')->nullable();
            $table->decimal('ha_restored_3year')->after('description')->nullable();
            $table->integer('trees_grown_total')->after('description')->nullable();
            $table->integer('trees_grown_3year')->after('description')->nullable();
            $table->integer('relevant_experience_years')->after('description')->nullable();
            $table->text('tree_care_approach')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
};
