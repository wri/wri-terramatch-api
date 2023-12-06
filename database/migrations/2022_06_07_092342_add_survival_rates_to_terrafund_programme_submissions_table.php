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
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->tinyInteger('percentage_survival_to_date');
            $table->tinyText('survival_calculation');
            $table->tinyText('survival_comparison');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'percentage_survival_to_date',
                'survival_calculation',
                'survival_comparison',
            ]);
        });
    }
};
