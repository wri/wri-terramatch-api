<?php

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
        Schema::table("audit_statuses", function (Blueprint $table) {
            $table->string("type")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("audit_statuses", function (Blueprint $table) {
            $table->enum("type", ["change-request", "change-request-updated", "status", "comment", "reminder-sent"])->nullable()->change();
        });
    }
};
