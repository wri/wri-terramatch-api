<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('organisation_versions', function (Blueprint $table) {
            $table->dropColumn('budget_21');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('organisation_versions', function (Blueprint $table) {
            $table->decimal('budget_21')->nullable();
        });
    }
};
