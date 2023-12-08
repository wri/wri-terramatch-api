<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePercentageSurvivalRateNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->unsignedInteger('percentage_survival_to_date')->nullable()->change();
            $table->text('survival_calculation')->nullable()->change();
            $table->text('survival_comparison')->nullable()->change();
        });
    }
}
