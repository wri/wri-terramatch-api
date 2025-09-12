<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'fin_budget_current_year',
                'fin_budget_1year',
                'fin_budget_2year',
                'fin_budget_3year',
                'organisation_revenue_this_year',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->decimal('fin_budget_current_year', 15, 2)->nullable();
            $table->decimal('fin_budget_1year', 15, 2)->nullable();
            $table->decimal('fin_budget_2year', 15, 2)->nullable();
            $table->decimal('fin_budget_3year', 15, 2)->nullable();
            $table->bigInteger('organisation_revenue_this_year')->nullable();
        });
    }
};
