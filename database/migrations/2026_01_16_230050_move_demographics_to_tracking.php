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
        Schema::rename('demographics', 'trackings');
        Schema::table('trackings', function (Blueprint $table): void {
            $table->string('domain')->nullable();
            $table->renameColumn('demographical_type', 'trackable_type');
            $table->renameColumn('demographical_id', 'trackable_id');
        });
        // Populate domain column
        DB::table('trackings')->update(['domain' => 'demographics']);
        // Make domain column not-nullable
        Schema::table('trackings', function (Blueprint $table): void {
            $table->string('domain')->nullable(false)->change();
        });

        Schema::rename('demographic_entries', 'tracking_entries');
        Schema::table('tracking_entries', function (Blueprint $table): void {
            $table->renameColumn('demographic_id', 'tracking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trackings', function (Blueprint $table): void {
            $table->dropColumn('domain');
            $table->renameColumn('trackable_type', 'demographical_type');
            $table->renameColumn('trackable_id', 'demographical_id');
        });
        Schema::rename('trackings', 'demographics');

        Schema::table('tracking_entries', function (Blueprint $table): void {
            $table->renameColumn('tracking_id', 'demographic_id');
        });
        Schema::rename('tracking_entries', 'demographic_entries');
    }
};
