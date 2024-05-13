<?php

use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            // the old definition uses a string, and hasn't yet been used for anything
            $table->dropColumn(['created_by']);
        });
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'created_by')->nullable();
        });
        Schema::table('polygon_geometry', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_polygon', function (Blueprint $table) {
            $table->dropColumn(['created_by']);
        });
        Schema::table('polygon_geometry', function (Blueprint $table) {
            $table->dropColumn(['created_by']);
        });
    }
};
