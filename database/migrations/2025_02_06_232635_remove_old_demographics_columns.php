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
        Schema::table('demographic_entries', function (Blueprint $table) {
            $table->dropColumn('demographical_type');
        });
        Schema::table('restoration_partners', function (Blueprint $table) {
            $table->drop();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
