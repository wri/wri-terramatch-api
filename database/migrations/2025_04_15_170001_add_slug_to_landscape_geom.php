<?php

use App\Models\LandscapeGeom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('landscape_geom', function (Blueprint $table) {
            $table->string('slug')->index();
        });

        // Normally we wouldn't do a data migration inline with a schema migration, but this one is very lightweight
        // and idempotent.
        LandscapeGeom::where('landscape', 'Ghana Cocoa Belt')->update(['slug' => 'gcb']);
        LandscapeGeom::where('landscape', 'Greater Rift Valley of Kenya')->update(['slug' => 'grv']);
        LandscapeGeom::where('landscape', 'Lake Kivu & Rusizi River Basin')->update(['slug' => 'ikr']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landscape_geom', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
