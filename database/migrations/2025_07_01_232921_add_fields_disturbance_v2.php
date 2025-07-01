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
        Schema::table('v2_disturbances', function (Blueprint $table) {
            $table->date('disturbance_date')->nullable()->after('disturbanceable_id');
            $table->string('subtype', 255)->nullable()->after('type');
            $table->unsignedInteger('people_affected')->nullable()->after('extent');
            $table->decimal('monetary_damage', 15, 2)->nullable()->after('people_affected');
            $table->text('action_description')->nullable()->after('description');
            $table->string('property_affected', 255)->nullable()->after('action_description');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_disturbances', function (Blueprint $table) {
            $table->dropColumn('disturbance_date');
            $table->dropColumn('subtype');
            $table->dropColumn('people_affected');
            $table->dropColumn('monetary_damage');
            $table->dropColumn('action_description');
            $table->dropColumn('property_affected');
        });
    }
};