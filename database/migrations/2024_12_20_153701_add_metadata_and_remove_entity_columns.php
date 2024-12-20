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
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('payload')->comment('Stores additional information for the delayed job.');

            $table->dropColumn(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->dropColumn('metadata');
            $table->unsignedBigInteger('entity_id')->nullable()->after('name');
            $table->string('entity_type')->nullable()->after('entityId');
        });
    }
};
