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
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->renameColumn('proccess_message', 'progress_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->renameColumn('progress_message', 'proccess_message');
        });
    }
};
