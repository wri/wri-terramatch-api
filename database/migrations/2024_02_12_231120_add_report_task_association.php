<?php

use App\Models\V2\Tasks\Task;
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
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->constrained(table: 'v2_tasks');
        });
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->constrained(table: 'v2_tasks');
        });
        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->constrained(table: 'v2_tasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn(['task_id']);
        });
        Schema::table('v2_site_reports', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn(['task_id']);
        });
        Schema::table('v2_nursery_reports', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn(['task_id']);
        });
    }
};
