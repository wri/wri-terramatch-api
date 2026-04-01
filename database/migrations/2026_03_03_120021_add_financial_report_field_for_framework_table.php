<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('frameworks', function (Blueprint $table) {
            $table->char('financial_report_form_uuid', 36)
                ->nullable()
                ->after('nursery_report_form_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frameworks', function (Blueprint $table) {
            $table->dropColumn('financial_report_form_uuid');
        });
    }
};
