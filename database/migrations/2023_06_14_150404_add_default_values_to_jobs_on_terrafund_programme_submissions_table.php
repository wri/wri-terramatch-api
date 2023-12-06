<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValuesToJobsOnTerrafundProgrammeSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->unsignedInteger('ft_men')->default(0)->change();
            $table->unsignedInteger('ft_total')->default(0)->change();
            $table->unsignedInteger('ft_women')->default(0)->change();
            $table->unsignedInteger('ft_youth')->default(0)->change();
            $table->unsignedInteger('pt_men')->default(0)->change();
            $table->unsignedInteger('pt_total')->default(0)->change();
            $table->unsignedInteger('pt_women')->default(0)->change();
            $table->unsignedInteger('pt_youth')->default(0)->change();
            $table->unsignedInteger('volunteer_men')->default(0)->change();
            $table->unsignedInteger('volunteer_total')->default(0)->change();
            $table->unsignedInteger('volunteer_women')->default(0)->change();
            $table->unsignedInteger('volunteer_youth')->default(0)->change();
        });
    }
}
