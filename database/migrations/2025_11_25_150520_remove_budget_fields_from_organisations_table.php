<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Soft delete all form_questions that reference the migrated financial fields
        $linkedFieldKeys = [
            'org-fin-bgt-cur-year',
            'org-fin-bgt-1year',
            'org-fin-bgt-2year',
            'org-fin-bgt-3year',
            'org-rev-this-year',
        ];

        DB::table('form_questions')
            ->whereIn('linked_field_key', $linkedFieldKeys)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        // Remove the columns from organisations table
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
        // Restore the columns in organisations table
        Schema::table('organisations', function (Blueprint $table) {
            $table->decimal('fin_budget_current_year', 15, 2)->nullable()->after('fin_start_month');
            $table->decimal('fin_budget_1year', 15, 2)->nullable()->after('fin_budget_current_year');
            $table->decimal('fin_budget_2year', 15, 2)->nullable()->after('fin_budget_1year');
            $table->decimal('fin_budget_3year', 15, 2)->nullable()->after('fin_budget_2year');
            $table->bigInteger('organisation_revenue_this_year')->nullable()->after('subtype');
        });

        // Restore soft deleted form_questions
        $linkedFieldKeys = [
            'org-fin-bgt-cur-year',
            'org-fin-bgt-1year',
            'org-fin-bgt-2year',
            'org-fin-bgt-3year',
            'org-rev-this-year',
        ];

        DB::table('form_questions')
            ->whereIn('linked_field_key', $linkedFieldKeys)
            ->whereNotNull('deleted_at')
            ->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);
    }
};