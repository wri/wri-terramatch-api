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
        Schema::table('srp_reports', function (Blueprint $table) {
            $table->renameColumn('other_restoration_partners_description', 'restoration_partners_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srp_reports', function (Blueprint $table) {
            $table->renameColumn('restoration_partners_description', 'other_restoration_partners_description');
        });
    }
};
