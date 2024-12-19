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
        Schema::table('v2_tree_species', function (Blueprint $table): void {
            if (Schema::hasColumn('v2_tree_species', 'old_model')) {
                $table->dropColumn('old_model');
            }
            if (Schema::hasColumn('v2_tree_species', 'old_id')) {
                $table->dropColumn('old_id');
            }
            if (Schema::hasColumn('v2_tree_species', 'type')) {
                $table->dropColumn('type');
            }

            $table->string('taxon_id')->nullable();
            $table->index('taxon_id');
        });

        Schema::table('v2_seedings', function (Blueprint $table): void {
            if (Schema::hasColumn('v2_seedings', 'old_model')) {
                $table->dropColumn('old_model');
            }
            if (Schema::hasColumn('v2_seedings', 'old_id')) {
                $table->dropColumn('old_id');
            }

            $table->string('taxon_id')->nullable();
            $table->index('taxon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_tree_species', function (Blueprint $table): void {
            $table->dropColumn('taxon_id');
        });
        Schema::table('v2_seedings', function (Blueprint $table): void {
            $table->dropColumn('taxon_id');
        });
    }
};
